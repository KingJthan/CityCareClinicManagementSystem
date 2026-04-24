from __future__ import annotations

import argparse
import html
import re
from pathlib import Path

from PIL import Image as PilImage
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, StyleSheet1, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    Image,
    ListFlowable,
    ListItem,
    PageBreak,
    Paragraph,
    Preformatted,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parent
SOURCE = ROOT / "project-documentation.md"
OUTPUT = ROOT / "CityCare_Project_Documentation.pdf"


def build_styles() -> StyleSheet1:
    styles = getSampleStyleSheet()
    styles["Title"].fontName = "Helvetica-Bold"
    styles["Title"].fontSize = 22
    styles["Title"].leading = 28
    styles["Title"].spaceAfter = 14
    styles["Title"].textColor = colors.HexColor("#0f3a52")

    styles["Heading1"].fontName = "Helvetica-Bold"
    styles["Heading1"].fontSize = 18
    styles["Heading1"].leading = 22
    styles["Heading1"].spaceBefore = 12
    styles["Heading1"].spaceAfter = 8
    styles["Heading1"].textColor = colors.HexColor("#0f3a52")

    styles["Heading2"].fontName = "Helvetica-Bold"
    styles["Heading2"].fontSize = 15
    styles["Heading2"].leading = 19
    styles["Heading2"].spaceBefore = 10
    styles["Heading2"].spaceAfter = 6
    styles["Heading2"].textColor = colors.HexColor("#156d7a")

    styles["Heading3"].fontName = "Helvetica-Bold"
    styles["Heading3"].fontSize = 12.5
    styles["Heading3"].leading = 16
    styles["Heading3"].spaceBefore = 10
    styles["Heading3"].spaceAfter = 6
    styles["Heading3"].textColor = colors.HexColor("#1f2937")

    styles["BodyText"].fontName = "Helvetica"
    styles["BodyText"].fontSize = 10.5
    styles["BodyText"].leading = 15
    styles["BodyText"].spaceAfter = 8

    styles.add(
        ParagraphStyle(
            name="SmallCaption",
            parent=styles["BodyText"],
            fontSize=9,
            leading=12,
            textColor=colors.HexColor("#52637a"),
            alignment=TA_CENTER,
            spaceAfter=10,
        )
    )
    styles.add(
        ParagraphStyle(
            name="CodeBlock",
            fontName="Courier",
            fontSize=8.8,
            leading=11.2,
            backColor=colors.HexColor("#f5f7fb"),
            borderColor=colors.HexColor("#dbe4f0"),
            borderPadding=8,
            borderWidth=0.7,
            borderRadius=4,
            spaceBefore=4,
            spaceAfter=10,
        )
    )
    styles.add(
        ParagraphStyle(
            name="TableCell",
            parent=styles["BodyText"],
            fontSize=9.5,
            leading=12,
        )
    )
    return styles


def inline_markup(text: str) -> str:
    text = html.escape(text.strip())
    text = re.sub(r"`([^`]+)`", r"<font name='Courier'>\1</font>", text)
    text = re.sub(r"\*\*(.+?)\*\*", r"<b>\1</b>", text)
    return text


def parse_table(lines: list[str]) -> list[list[str]]:
    rows: list[list[str]] = []
    for line in lines:
        stripped = line.strip().strip("|")
        rows.append([cell.strip() for cell in stripped.split("|")])

    if len(rows) > 1 and all(re.fullmatch(r":?-{3,}:?", cell or "") for cell in rows[1]):
        rows.pop(1)

    return rows


def make_image(path_text: str, alt_text: str, max_width: float, max_height: float, styles: StyleSheet1):
    image_path = (ROOT / path_text).resolve()
    if not image_path.exists():
        return [Paragraph(f"Missing image: {html.escape(path_text)}", styles["BodyText"]), Spacer(1, 6)]

    with PilImage.open(image_path) as img:
        width, height = img.size

    scale = min(max_width / width, max_height / height, 1)
    rendered = Image(str(image_path), width=width * scale, height=height * scale)
    rendered.hAlign = "CENTER"
    return [rendered, Spacer(1, 4), Paragraph(inline_markup(alt_text), styles["SmallCaption"])]


def add_page_number(canvas, doc):
    page_number = canvas.getPageNumber()
    canvas.setFont("Helvetica", 9)
    canvas.setFillColor(colors.HexColor("#607086"))
    canvas.drawRightString(doc.pagesize[0] - doc.rightMargin, 18, f"Page {page_number}")


def markdown_to_story(text: str, styles: StyleSheet1, doc_width: float) -> list:
    lines = text.splitlines()
    story: list = []
    paragraph_lines: list[str] = []
    screenshot_section = False
    started_screenshot_item = False
    max_image_height = 6.6 * inch

    def flush_paragraph() -> None:
        nonlocal paragraph_lines
        if not paragraph_lines:
            return
        paragraph = " ".join(line.strip() for line in paragraph_lines).strip()
        if paragraph:
            story.append(Paragraph(inline_markup(paragraph), styles["BodyText"]))
        paragraph_lines = []

    i = 0
    while i < len(lines):
        line = lines[i]
        stripped = line.strip()

        if stripped == "":
            flush_paragraph()
            i += 1
            continue

        heading_match = re.match(r"^(#{1,3})\s+(.*)$", stripped)
        if heading_match:
            flush_paragraph()
            level = len(heading_match.group(1))
            text_value = heading_match.group(2).strip()

            if level == 2:
                screenshot_section = text_value == "6. Screenshots and Page Purposes"
                started_screenshot_item = False

            if screenshot_section and level == 3:
                if started_screenshot_item:
                    story.append(PageBreak())
                started_screenshot_item = True

            style_name = {1: "Title", 2: "Heading1", 3: "Heading2"}[level]
            story.append(Paragraph(inline_markup(text_value), styles[style_name]))
            i += 1
            continue

        image_match = re.match(r"^!\[(.*?)\]\((.*?)\)$", stripped)
        if image_match:
            flush_paragraph()
            story.extend(make_image(image_match.group(2), image_match.group(1), doc_width, max_image_height, styles))
            i += 1
            continue

        if stripped.startswith("```"):
            flush_paragraph()
            fence = stripped
            code_lines: list[str] = []
            i += 1
            while i < len(lines) and not lines[i].strip().startswith("```"):
                code_lines.append(lines[i].rstrip("\n"))
                i += 1
            story.append(Preformatted("\n".join(code_lines), styles["CodeBlock"]))
            if i < len(lines) and lines[i].strip().startswith("```") and lines[i].strip() != fence:
                i += 1
            else:
                i += 1
            continue

        if stripped.startswith("|"):
            flush_paragraph()
            table_lines: list[str] = []
            while i < len(lines) and lines[i].strip().startswith("|"):
                table_lines.append(lines[i])
                i += 1
            rows = parse_table(table_lines)
            table_data = [
                [Paragraph(inline_markup(cell), styles["TableCell"]) for cell in row]
                for row in rows
            ]
            table = Table(table_data, repeatRows=1)
            table.setStyle(
                TableStyle(
                    [
                        ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#eaf2f8")),
                        ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#0f3a52")),
                        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                        ("GRID", (0, 0), (-1, -1), 0.6, colors.HexColor("#cfdae6")),
                        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, colors.HexColor("#f8fbfd")]),
                        ("LEFTPADDING", (0, 0), (-1, -1), 6),
                        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
                        ("TOPPADDING", (0, 0), (-1, -1), 5),
                        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
                    ]
                )
            )
            story.append(table)
            story.append(Spacer(1, 8))
            continue

        bullet_match = re.match(r"^[-*]\s+(.*)$", stripped)
        if bullet_match:
            flush_paragraph()
            items: list[str] = []
            while i < len(lines):
                current = lines[i].strip()
                match = re.match(r"^[-*]\s+(.*)$", current)
                if not match:
                    break
                items.append(match.group(1).strip())
                i += 1
            list_items = [ListItem(Paragraph(inline_markup(item), styles["BodyText"])) for item in items]
            story.append(ListFlowable(list_items, bulletType="bullet", start="circle", leftPadding=18))
            story.append(Spacer(1, 6))
            continue

        number_match = re.match(r"^\d+\.\s+(.*)$", stripped)
        if number_match:
            flush_paragraph()
            items: list[str] = []
            while i < len(lines):
                current = lines[i].strip()
                match = re.match(r"^\d+\.\s+(.*)$", current)
                if not match:
                    break
                items.append(match.group(1).strip())
                i += 1
            list_items = [ListItem(Paragraph(inline_markup(item), styles["BodyText"])) for item in items]
            story.append(ListFlowable(list_items, bulletType="1", leftPadding=18))
            story.append(Spacer(1, 6))
            continue

        paragraph_lines.append(line)
        i += 1

    flush_paragraph()
    return story


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--output", type=Path, default=OUTPUT)
    args = parser.parse_args()

    styles = build_styles()
    text = SOURCE.read_text(encoding="utf-8")
    doc = SimpleDocTemplate(
        str(args.output),
        pagesize=A4,
        title="CityCare Clinic Appointment and Patient Management System",
        author="Jonathan Mugume",
        leftMargin=42,
        rightMargin=42,
        topMargin=40,
        bottomMargin=28,
    )
    story = markdown_to_story(text, styles, doc.width)
    doc.build(story, onFirstPage=add_page_number, onLaterPages=add_page_number)


if __name__ == "__main__":
    main()
