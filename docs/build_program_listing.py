# -*- coding: utf-8 -*-
"""Program Listing for Panorama Montessori School LMS, objectives 1.1–1.9."""

import os
from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Inches, Pt, RGBColor

ROOT = r"c:\Laravel\Capstone-Project\lms"
DOCS = r"c:\Laravel\Capstone-Project\docs"
LISTING = os.path.join(DOCS, "Program Listing.docx")
SOURCE = os.path.join(DOCS, "Program Listing Source Code.docx")

BLUE = RGBColor(0x3D, 0x5E, 0xE1)
INK = RGBColor(0x1F, 0x29, 0x37)
MUTED = RGBColor(0x4B, 0x55, 0x63)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)


def shade(cell, hex_color):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:fill"), hex_color)
    shd.set(qn("w:val"), "clear")
    tcPr.append(shd)


def font(run, size=11, bold=False, italic=False, color=INK, name="Calibri"):
    run.font.name = name
    run._element.rPr.rFonts.set(qn("w:eastAsia"), name)
    run.font.size = Pt(size)
    run.bold = bold
    run.italic = italic
    run.font.color.rgb = color


def P(doc, text, size=11, bold=False, italic=False, color=INK, before=0, after=8, align="left", name="Calibri"):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(before)
    p.paragraph_format.space_after = Pt(after)
    p.paragraph_format.line_spacing = 1.08
    if align == "center":
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    elif align == "justify":
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    run = p.add_run(text)
    font(run, size, bold, italic, color, name)
    return p


def H(doc, text, level):
    p = doc.add_heading(text, level=level)
    size = {1: 18, 2: 14, 3: 12}[level]
    color = BLUE if level != 2 else INK
    for run in p.runs:
        font(run, size, True, False, color)
    p.paragraph_format.space_before = Pt(14 if level == 1 else 10)
    p.paragraph_format.space_after = Pt(4)
    return p


def bullets(doc, items):
    for item in items:
        p = doc.add_paragraph(style="List Bullet")
        p.clear()
        p.paragraph_format.space_after = Pt(2)
        run = p.add_run(item)
        font(run, 11)


def table(doc, headers, rows):
    t = doc.add_table(rows=1, cols=len(headers))
    t.style = "Table Grid"
    for i, text in enumerate(headers):
        cell = t.rows[0].cells[i]
        cell.text = ""
        shade(cell, "3D5EE1")
        run = cell.paragraphs[0].add_run(text)
        font(run, 9, True, False, WHITE)
    for row in rows:
        cells = t.add_row().cells
        for i, text in enumerate(row):
            cells[i].text = ""
            run = cells[i].paragraphs[0].add_run(text)
            font(run, 8)
    P(doc, "", after=4)


def header_footer(section):
    hp = section.header.paragraphs[0]
    r = hp.add_run("Panorama Montessori School, Inc.  ·  Program Listing")
    font(r, 9, True, False, BLUE)
    fp = section.footer.paragraphs[0]
    fp.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    r = fp.add_run("LMS source listing  ·  Page ")
    font(r, 9, False, False, MUTED)
    begin = OxmlElement("w:fldChar")
    begin.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = " PAGE "
    end = OxmlElement("w:fldChar")
    end.set(qn("w:fldCharType"), "end")
    fp.add_run()._r.append(begin)
    rr = fp.add_run()
    rr._r.append(instr)
    font(rr, 9, False, False, MUTED)
    fp.add_run()._r.append(end)


def setup(doc):
    sec = doc.sections[0]
    sec.page_width = Inches(8.5)
    sec.page_height = Inches(11)
    sec.top_margin = Inches(0.75)
    sec.bottom_margin = Inches(0.7)
    sec.left_margin = Inches(0.75)
    sec.right_margin = Inches(0.75)
    header_footer(sec)
    normal = doc.styles["Normal"]
    normal.font.name = "Calibri"
    normal.font.size = Pt(11)
    normal.font.color.rgb = INK


def blades(folder):
    base = os.path.join(ROOT, "resources", "views", folder)
    found = []
    if not os.path.isdir(base):
        return found
    for dirpath, _, files in os.walk(base):
        for name in files:
            if name.endswith(".blade.php"):
                rel = os.path.relpath(os.path.join(dirpath, name), os.path.join(ROOT, "resources", "views"))
                found.append(rel.replace("\\", "/"))
    return sorted(found)


# path relative to lms/, purpose. Printed in the source-code file when print_code is True.
MODULES = [
    {
        "id": "1.1",
        "title": "Managing student information and enrollment records",
        "roles": "Administrator keeps student, parent, and enrollment records. The registrar reviews applications. Families submit applications on the public portal. Grade 7–10 students and parents of Nursery–Grade 6 later view the accepted record from their own menus.",
        "interface": "User Management (All Users, Create User, Students, Parents, Enrollments), Student Promotions, the registrar Applications page, and the public Enrollment Portal for a new or returning learner.",
        "files": [
            ("app/Http/Controllers/StudentController.php", "Student information: list, profile, add, edit, and restore."),
            ("app/Http/Controllers/EnrollmentController.php", "Enrollment records for a student, subject, and academic year."),
        ],
        "views": ["student", "enrollments", "enrollment", "promotions", "usermanagement"],
    },
    {
        "id": "1.2",
        "title": "Managing subjects, sections, curriculum, and schedules",
        "roles": "Administrator builds the year, semesters, subjects, sections, curriculum, and weekly schedule. Teachers see only the classes assigned to them. Students and parents see the resulting timetable.",
        "interface": "Academic Management: Classes & Subjects, Curriculum, Class Schedules, Academic Years, and Semesters. Teachers open My Schedule and My Classes & Subjects.",
        "files": [
            ("app/Http/Controllers/ClassSubjectController.php", "Subjects and sections, and the teacher assigned to each for the year."),
            ("app/Http/Controllers/CurriculumController.php", "Curriculum and the subjects offered for each grade."),
            ("app/Http/Controllers/ClassScheduleController.php", "Class schedule meetings: subject, section, day, and time."),
        ],
        "views": ["academic_years", "semesters", "subjects", "sections", "curriculum", "class-subject", "schedule", "admin"],
    },
    {
        "id": "1.3",
        "title": "Recording and monitoring student attendance",
        "roles": "Teachers mark attendance for their classes. Students in Grade 7–10 and parents of Nursery–Grade 6 view the saved marks. Administrators monitor attendance through reports and analytics.",
        "interface": "Teacher menu: Class Management → Attendance. Student menu: Academic Records → Attendance. Parent menu: Attendance.",
        "files": [
            ("app/Http/Controllers/AttendanceController.php", "Records attendance and shows it to the teacher, student, and parent."),
        ],
        "views": ["attendance"],
    },
    {
        "id": "1.4",
        "title": "Encoding, computing, and monitoring student grades and academic performance",
        "roles": "A teacher encodes official grades only after an administrator grants Grade Encoding Access. Nursery–Grade 3 uses descriptive letters. Grade 4–Grade 10 uses numbers, with 75 percent as the passing mark. Students and parents view released grades. Administrators watch progress on the Performance Hub.",
        "interface": "Grade Encoding Access, Grade Entry, Performance Hub, Grading Progress, and the student Grades page.",
        "files": [
            ("app/Http/Controllers/GradingController.php", "Encodes grades, computes the term result, and blocks a number grade for Nursery–Grade 3."),
        ],
        "views": ["grading", "admin"],
    },
    {
        "id": "1.5",
        "title": "Managing lessons, learning materials, and student activities",
        "roles": "Teachers create lessons, quizzes, practice work, projects, reflections, and file assignments. Students open the work, answer quizzes, and upload the required file. Parents do not encode classwork.",
        "interface": "Teacher: My Lessons and All Assignments. Student: the subject under My Classes, the quiz page, and the assignment page.",
        "files": [
            ("app/Http/Controllers/LessonController.php", "Lessons and the learning materials posted on a lesson."),
            ("app/Http/Controllers/ActivityController.php", "Student activities: create, post, close, and review submissions."),
        ],
        "views": ["lessons", "activities", "quizzes", "assignments", "student"],
    },
    {
        "id": "1.6",
        "title": "Communicating announcements, reminders, and academic updates",
        "roles": "Administrators post school notices. Teachers post to their sections and answer consultation requests. Students, parents, and the registrar read the notices and messages addressed to them.",
        "interface": "Announcements, Chat in the header, Consultations, class posts, and Calendar & Events.",
        "files": [
            ("app/Http/Controllers/AnnouncementController.php", "Announcements and academic updates for administrators, teachers, students, and parents."),
        ],
        "views": ["announcements", "chat", "consultations", "class-posts", "calendar", "notifications"],
    },
    {
        "id": "1.7",
        "title": "Viewing academic information through role-based student and parent portals",
        "roles": "Grade 7–10 students sign in to My Classes, My Schedule, Grades, and Attendance. Nursery–Grade 6 has no student login. The parent account opens My Children and the same academic information for the linked learner.",
        "interface": "Student sidebar and parent sidebar. The menu is chosen from the signed-in role.",
        "files": [
            ("app/Http/Controllers/ParentController.php", "Parent portal for a linked child’s grades, attendance, and activities. Grade 7–10 students open their own grades from StudentController."),
        ],
        "views": ["parent", "dashboard", "partials", "sidebar"],
    },
    {
        "id": "1.8",
        "title": "Monitoring academic performance and attendance through dashboards and analytics",
        "roles": "Administrators open School Analytics, the Performance Hub, and Grading Progress. Teachers open the class Performance Hub. Students open My Analytics. Figures read the current academic year and stay empty when that year has no rows yet.",
        "interface": "Dashboard, School Analytics, Performance Hub, Grading Progress, and My Analytics.",
        "files": [
            ("app/Http/Controllers/AnalyticsController.php", "Dashboards for school, teacher, and student performance and attendance."),
        ],
        "views": ["analytics", "grading", "dashboard"],
    },
    {
        "id": "1.9",
        "title": "Generating academic reports and documents",
        "roles": "Administrators and teachers generate a transcript, class list, grade slip, or progress report for one student or a whole section. Other reports are viewed or downloaded from the report library. Each person sees only the reports allowed for that role. Nursery and Kindergarten documents use descriptive letters. Grade 4–Grade 10 documents use numerical scores.",
        "interface": "Reports & Documents, including Generate/Download and View Report.",
        "files": [
            ("app/Http/Controllers/ReportController.php", "Generates the transcript, class list, grade slip, and progress report."),
            ("app/Http/Controllers/SchoolReportController.php", "Opens and downloads one report from the report library."),
        ],
        "views": ["reports"],
    },
]


def existing(rel):
    path = os.path.join(ROOT, rel.replace("/", os.sep))
    return os.path.isfile(path)


def read_source(rel, limit=60):
    path = os.path.join(ROOT, rel.replace("/", os.sep))
    with open(path, "r", encoding="utf-8", errors="replace") as handle:
        lines = handle.read().replace("\r\n", "\n").replace("\r", "\n").split("\n")
    shown = lines[:limit]
    return "\n".join(shown)


def add_code_block(doc, text):
    lines = text.split("\n")
    chunk = []
    def flush(block):
        if not block:
            return
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(0)
        p.paragraph_format.space_after = Pt(0)
        p.paragraph_format.line_spacing = 1.0
        for i, line in enumerate(block):
            run = p.add_run(line if line else " ")
            font(run, 8, False, False, INK, "Consolas")
            if i < len(block) - 1:
                run.add_break()
    for line in lines:
        chunk.append(line)
        if len(chunk) >= 60:
            flush(chunk)
            chunk = []
    flush(chunk)


def build_listing():
    doc = Document()
    setup(doc)
    P(doc, "PANORAMA MONTESSORI SCHOOL, INC.", 13, True, False, BLUE, align="center", after=2)
    P(doc, "Santa Rosa Campus", 11, False, False, MUTED, align="center", after=10)
    P(doc, "Program Listing", 26, True, False, INK, align="center", after=2)
    P(doc, "Source files for the role-based academic management system", 12, False, True, MUTED, align="center", after=8)
    P(doc, "Objective 1. Design a user-friendly and role-based interface for academic management.", 11, align="center", after=2)
    P(doc, "Objectives 1.1 to 1.9", 11, False, False, MUTED, align="center", after=8)
    P(doc, "October 2026", 11, False, False, MUTED, align="center")
    doc.add_page_break()

    H(doc, "How to read this listing", 1)
    P(doc, "Each objective has only the controller that performs that task. Models, screens, routes, and supporting classes are not included. The companion file prints those same controllers.", align="justify")
    bullets(doc, [
        "Administrator — year, people, classes, schedules, encoding access, analytics, and school reports.",
        "Registrar — enrollment applications and enrollment reports.",
        "Teacher — assigned classes, lessons, activities, attendance, announcements, and grades when encoding is granted.",
        "Student — Grade 7 to Grade 10 classwork, schedule, grades, and attendance.",
        "Parent — Nursery to Grade 6 children. Those learners do not have a separate login.",
    ])

    H(doc, "Master list of source file names", 1)
    rows = []
    seen = set()
    n = 1
    for module in MODULES:
        for rel, purpose in module["files"]:
            if rel in seen or not existing(rel):
                continue
            seen.add(rel)
            rows.append((str(n), os.path.basename(rel), module["id"], rel))
            n += 1
    table(doc, ["No.", "Source file name", "Objective", "Path"], rows)
    P(doc, f"{len(rows)} source files are listed. Each one is printed in Program Listing Source Code.docx.", 10, False, True, MUTED)

    for module in MODULES:
        H(doc, f"Objective {module['id']}", 1)
        H(doc, module["title"], 2)
        P(doc, module["roles"], align="justify")
        P(doc, "Screens", 12, True, False, BLUE, before=4, after=2)
        P(doc, module["interface"], align="justify")
        P(doc, "Source files", 12, True, False, BLUE, before=6, after=2)
        file_rows = []
        missing = []
        for i, (rel, purpose) in enumerate(module["files"], 1):
            name = os.path.basename(rel)
            if existing(rel):
                file_rows.append((str(i), name, rel, purpose))
            else:
                missing.append(name)
        table(doc, ["No.", "Source file name", "Path", "What this file does"], file_rows)
        if missing:
            P(doc, "Not found in the project and omitted from the source print: " + ", ".join(missing), 10, False, True, MUTED)

    H(doc, "Source-code companion", 1)
    P(doc, "Open Program Listing Source Code.docx for the printed source. Each program shows only the first 60 lines.", align="justify")
    doc.core_properties.title = "Program Listing"
    doc.core_properties.subject = "Source file listing for LMS objectives 1.1 to 1.9"
    doc.core_properties.category = "Program Listing"
    doc.save(LISTING)
    print("listing", LISTING, "files", len(rows))


def build_source():
    doc = Document()
    setup(doc)
    P(doc, "PANORAMA MONTESSORI SCHOOL, INC.", 13, True, False, BLUE, align="center", after=2)
    P(doc, "Program Listing", 26, True, False, INK, align="center", after=2)
    P(doc, "Source code", 18, True, False, BLUE, align="center", after=8)
    P(doc, "File name of this document: Program Listing Source Code", 11, align="center")
    P(doc, "Each program shows the first 60 lines only.", 11, align="center")
    doc.add_page_break()

    printed = {}
    count = 0
    for module in MODULES:
        for rel, purpose in module["files"]:
            if not existing(rel):
                continue
            if count:
                doc.add_page_break()
            count += 1
            name = os.path.basename(rel)
            H(doc, f"Program Listing {module['id']}  ·  {name}", 1)
            P(doc, "Source file name: " + name, 11, True, after=1)
            P(doc, "Path: lms/" + rel, 10, False, False, MUTED, after=1)
            P(doc, "Objective " + module["id"] + ". " + module["title"], 10, False, False, MUTED, after=1)
            P(doc, purpose, 11, after=6)
            if rel in printed:
                P(doc, "This source is printed in full under Objective " + printed[rel] + ". It is not repeated here.", 11)
            else:
                printed[rel] = module["id"]
                add_code_block(doc, read_source(rel, 60))
                P(doc, "Listing stops at line 60.", 10, False, True, MUTED, before=6)
            print("printed", name)
    doc.core_properties.title = "Program Listing Source Code"
    doc.core_properties.subject = "LMS source code for objectives 1.1 to 1.9"
    doc.core_properties.category = "Program Listing"
    doc.save(SOURCE)
    print("source", SOURCE, "files", count)


if __name__ == "__main__":
    build_listing()
    build_source()
