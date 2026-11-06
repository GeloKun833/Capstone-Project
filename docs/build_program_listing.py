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


def read_source(rel):
    path = os.path.join(ROOT, rel.replace("/", os.sep))
    with open(path, "r", encoding="utf-8", errors="replace") as handle:
        return handle.read().replace("\r\n", "\n").replace("\r", "\n")


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


MODULE_ORDER = [m["id"] for m in MODULES] + ["Shared", "Other"]

MODULE_TITLES = {m["id"]: m["title"] for m in MODULES}
MODULE_TITLES["Shared"] = "Application foundation used by every module"
MODULE_TITLES["Other"] = "Fees, settings, backup, and other project source"


def classify(rel):
    r = rel.replace("\\", "/").lower()
    base = os.path.basename(r)

    if r.startswith("resources/views/student/assignments/") or "lesson-show" in r or r.startswith("resources/views/student/lesson"):
        return "1.5"
    if r.startswith("resources/views/student/") and base in {
        "grades.blade.php", "my-classes.blade.php", "class-detail.blade.php",
    }:
        return "1.7"
    if "attendance" in r:
        return "1.3"
    if any(k in r for k in (
        "lesson", "activity", "quiz", "assignment", "submission", "safeupload",
    )):
        return "1.5"
    if any(k in r for k in (
        "grading", "gradecontroller", "grade.php", "gradesexport", "quarterly",
        "studentgpa", "gradealert", "observed", "weightsetting", "descriptive",
        "academicthreshold", "gradeencoding", "schoolquarter", "gradenarrative",
        "gradesubject", "/gpa", "gpaexport", "gpa-pdf", "grades-pdf", "grades.blade",
    )):
        return "1.4"
    if any(k in r for k in ("report", "transcript", "export")):
        return "1.9"
    if any(k in r for k in ("analytics", "performancehub", "performance-hub", "performance-analytics", "studentperformance")):
        return "1.8"
    if any(k in r for k in (
        "announcement", "chat", "message", "notification", "consultation",
        "class-post", "classpost", "calendar",
    )):
        return "1.6"
    if any(k in r for k in (
        "subject", "section", "curriculum", "schedule", "academicyear", "academic_year",
        "semester", "teacher", "room.php", "/room", "department", "classsubject", "class-subject",
    )):
        return "1.2"
    if any(k in r for k in (
        "parent", "sidebar", "dashboard", "homecontroller", "/auth/", "layouts/", "help/",
    )):
        return "1.7"
    if any(k in r for k in (
        "student", "enrollment", "promotion", "usermanagement", "user.php", "userobserver",
    )):
        return "1.1"
    if any(k in r for k in (
        "invoice", "fees", "account", "backup", "setting", "menu", "demo", "dummy",
    )):
        return "Other"
    return "Shared"


def collect_project_files():
    groups = {key: [] for key in MODULE_ORDER}
    skip_dirs = {"vendor", "node_modules", "storage", ".git", "plugins"}
    exts = (".php", ".js", ".css", ".blade.php")
    for dirpath, dirs, files in os.walk(ROOT):
        dirs[:] = [d for d in dirs if d not in skip_dirs]
        for name in files:
            if not name.endswith(exts):
                continue
            full = os.path.join(dirpath, name)
            rel = os.path.relpath(full, ROOT).replace("\\", "/")
            if rel.startswith("public/assets/plugins/") or "/plugins/" in rel:
                continue
            groups[classify(rel)].append(rel)
    for key in groups:
        groups[key].sort()
    return groups


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

    groups = collect_project_files()
    total = sum(len(v) for v in groups.values())
    H(doc, "How to read this listing", 1)
    P(doc, "Every project source file is listed once, under the module it belongs to. The companion file prints the complete code of each file, module by module. Third-party libraries in vendor and public plugins are not part of this project’s source.", align="justify")

    H(doc, "Master list of source file names", 1)
    rows = []
    n = 1
    for module_id in MODULE_ORDER:
        for rel in groups[module_id]:
            rows.append((str(n), os.path.basename(rel), module_id, rel))
            n += 1
    table(doc, ["No.", "Source file name", "Module", "Path"], rows)
    P(doc, f"{total} source files are listed. The complete code of each file is in Program Listing Source Code.docx.", 10, False, True, MUTED)

    roles = {m["id"]: m["roles"] for m in MODULES}
    for module_id in MODULE_ORDER:
        H(doc, f"Module {module_id}", 1)
        H(doc, MODULE_TITLES[module_id], 2)
        if module_id in roles:
            P(doc, roles[module_id], align="justify")
        files = groups[module_id]
        P(doc, f"{len(files)} source files", 11, True, False, BLUE, before=4, after=2)
        table(doc, ["No.", "Source file name", "Path"], [
            (str(i), os.path.basename(rel), rel) for i, rel in enumerate(files, 1)
        ])

    H(doc, "Source-code companion", 1)
    P(doc, "Open Program Listing Source Code.docx for the complete source of every file in each module.", align="justify")
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
    P(doc, "The source below is the complete code of every project file, grouped by module.", 11, align="center")

    groups = collect_project_files()
    count = 0
    for module_id in MODULE_ORDER:
        files = groups[module_id]
        if not files:
            continue
        doc.add_page_break()
        H(doc, f"Module {module_id}", 1)
        H(doc, MODULE_TITLES[module_id], 2)
        P(doc, f"{len(files)} source files. The complete code of each file follows.", 11)
        for rel in files:
            doc.add_page_break()
            count += 1
            name = os.path.basename(rel)
            H(doc, f"Module {module_id}  ·  {name}", 1)
            P(doc, "Source file name: " + name, 11, True, after=1)
            P(doc, "Path: lms/" + rel, 10, False, False, MUTED, after=6)
            add_code_block(doc, read_source(rel))
            if count % 25 == 0:
                print("printed", count, name)
    print("source files", count)
    doc.core_properties.title = "Program Listing Source Code"
    doc.core_properties.subject = "LMS source code for objectives 1.1 to 1.9"
    doc.core_properties.category = "Program Listing"
    doc.save(SOURCE)
    print("source", SOURCE, "files", count)


if __name__ == "__main__":
    build_listing()
    build_source()
