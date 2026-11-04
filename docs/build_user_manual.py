# -*- coding: utf-8 -*-
"""Build the Panorama Montessori School LMS user manual."""

from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Inches, Pt, RGBColor, Twips

OUT = r"c:\Laravel\Capstone-Project\docs\Panorama Montessori School LMS User Manual.docx"

BLUE = RGBColor(0x3D, 0x5E, 0xE1)
INK = RGBColor(0x1F, 0x29, 0x37)
MUTED = RGBColor(0x4B, 0x55, 0x63)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)

FIGURES = []


def shade(cell, hex_color):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:fill"), hex_color)
    shd.set(qn("w:val"), "clear")
    tcPr.append(shd)


def set_borders(cell, color="3D5EE1", size="12"):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    borders = OxmlElement("w:tcBorders")
    for edge in ("top", "left", "bottom", "right"):
        el = OxmlElement(f"w:{edge}")
        el.set(qn("w:val"), "single")
        el.set(qn("w:sz"), size)
        el.set(qn("w:space"), "0")
        el.set(qn("w:color"), color)
        borders.append(el)
    tcPr.append(borders)


def set_cell_margin(cell, top=80, bottom=80, left=120, right=120):
    tc = cell._tc
    tcPr = tc.get_or_add_tcPr()
    mar = OxmlElement("w:tcMar")
    for name, val in (("top", top), ("left", left), ("bottom", bottom), ("right", right)):
        node = OxmlElement(f"w:{name}")
        node.set(qn("w:w"), str(val))
        node.set(qn("w:type"), "dxa")
        mar.append(node)
    tcPr.append(mar)


def prevent_row_split(row):
    tr = row._tr
    trPr = tr.get_or_add_trPr()
    cant = OxmlElement("w:cantSplit")
    trPr.append(cant)


def run_font(run, name="Calibri", size=11, bold=False, italic=False, color=INK):
    run.font.name = name
    run._element.rPr.rFonts.set(qn("w:eastAsia"), name)
    run.font.size = Pt(size)
    run.bold = bold
    run.italic = italic
    run.font.color.rgb = color


def add_text(p, text, **kwargs):
    run = p.add_run(text)
    run_font(run, **kwargs)
    return run


def para(doc, text, size=11, bold=False, italic=False, color=INK, space_before=0, space_after=8, align="left"):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.12
    if align == "center":
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    elif align == "justify":
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    add_text(p, text, size=size, bold=bold, italic=italic, color=color)
    return p


def rich(doc, parts, space_before=0, space_after=8, align="left"):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.12
    if align == "justify":
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    for text, kwargs in parts:
        add_text(p, text, **kwargs)
    return p


def h1(doc, text):
    p = doc.add_heading(text, level=1)
    for run in p.runs:
        run_font(run, size=20, bold=True, color=BLUE)
    p.paragraph_format.space_before = Pt(16)
    p.paragraph_format.space_after = Pt(8)
    return p


def h2(doc, text):
    p = doc.add_heading(text, level=2)
    for run in p.runs:
        run_font(run, size=15, bold=True, color=INK)
    p.paragraph_format.space_before = Pt(14)
    p.paragraph_format.space_after = Pt(4)
    return p


def h3(doc, text):
    p = doc.add_heading(text, level=3)
    for run in p.runs:
        run_font(run, size=12, bold=True, color=BLUE)
    p.paragraph_format.space_before = Pt(10)
    p.paragraph_format.space_after = Pt(2)
    return p


def bullets(doc, items):
    for item in items:
        p = doc.add_paragraph(style="List Bullet")
        p.clear()
        p.paragraph_format.space_after = Pt(2)
        p.paragraph_format.space_before = Pt(0)
        add_text(p, item, size=11)


def steps(doc, items):
    for i, item in enumerate(items, 1):
        p = doc.add_paragraph()
        p.paragraph_format.left_indent = Cm(0.75)
        p.paragraph_format.first_line_indent = Cm(-0.75)
        p.paragraph_format.space_after = Pt(3)
        p.paragraph_format.space_before = Pt(0)
        add_text(p, f"{i}.  ", size=11, bold=True, color=BLUE)
        add_text(p, item, size=11)


def callout(doc, label, text, fill="EEF2FF"):
    table = doc.add_table(rows=1, cols=1)
    table.autofit = True
    cell = table.cell(0, 0)
    shade(cell, fill)
    set_borders(cell, "3D5EE1", "8")
    set_cell_margin(cell, 80, 80, 140, 140)
    cell.text = ""
    p1 = cell.paragraphs[0]
    p1.paragraph_format.space_after = Pt(2)
    add_text(p1, label, size=10, bold=True, color=BLUE)
    p2 = cell.add_paragraph()
    p2.paragraph_format.space_after = Pt(0)
    add_text(p2, text, size=10.5, color=INK)
    doc.add_paragraph().paragraph_format.space_after = Pt(4)


def figure(doc, title, capture, signed_in):
    n = len(FIGURES) + 1
    FIGURES.append((n, title, capture, signed_in))
    cap = doc.add_paragraph()
    cap.paragraph_format.space_before = Pt(10)
    cap.paragraph_format.space_after = Pt(2)
    add_text(cap, f"Figure {n}.  {title}", size=11, bold=True, color=BLUE)

    table = doc.add_table(rows=1, cols=1)
    table.autofit = True
    cell = table.cell(0, 0)
    shade(cell, "F4F7FF")
    set_borders(cell, "3D5EE1", "16")
    set_cell_margin(cell, 160, 140, 160, 160)
    cell.text = ""
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_after = Pt(4)
    add_text(p, "INSERT YOUR SCREENSHOT HERE", size=13, bold=True, color=BLUE)
    p2 = cell.add_paragraph()
    p2.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p2.paragraph_format.space_after = Pt(2)
    add_text(p2, "Click inside this box, delete this instruction, then choose Insert → Pictures.", size=10, italic=True, color=MUTED)
    p3 = cell.add_paragraph()
    p3.alignment = WD_ALIGN_PARAGRAPH.LEFT
    p3.paragraph_format.space_before = Pt(8)
    p3.paragraph_format.space_after = Pt(0)
    add_text(p3, "Who is signed in: ", size=10, bold=True, color=INK)
    add_text(p3, signed_in, size=10, color=INK)
    p4 = cell.add_paragraph()
    p4.paragraph_format.space_after = Pt(0)
    add_text(p4, "What to show: ", size=10, bold=True, color=INK)
    add_text(p4, capture, size=10, color=INK)
    spacer = doc.add_paragraph()
    spacer.paragraph_format.space_after = Pt(6)


def simple_table(doc, headers, rows):
    table = doc.add_table(rows=1, cols=len(headers))
    table.style = "Table Grid"
    hdr = table.rows[0].cells
    for i, text in enumerate(headers):
        hdr[i].text = ""
        shade(hdr[i], "3D5EE1")
        p = hdr[i].paragraphs[0]
        add_text(p, text, size=10, bold=True, color=WHITE)
    for row in rows:
        cells = table.add_row().cells
        for i, text in enumerate(row):
            cells[i].text = ""
            p = cells[i].paragraphs[0]
            add_text(p, text, size=10)
    doc.add_paragraph().paragraph_format.space_after = Pt(4)


def header_footer(section):
    header = section.header
    header.is_linked_to_previous = False
    hp = header.paragraphs[0]
    hp.alignment = WD_ALIGN_PARAGRAPH.LEFT
    add_text(hp, "Panorama Montessori School, Inc.", size=9, bold=True, color=BLUE)
    add_text(hp, "   ·   Learning Management System User Manual", size=9, color=MUTED)
    pPr = hp._p.get_or_add_pPr()
    pBdr = OxmlElement("w:pBdr")
    bottom = OxmlElement("w:bottom")
    bottom.set(qn("w:val"), "single")
    bottom.set(qn("w:sz"), "8")
    bottom.set(qn("w:space"), "6")
    bottom.set(qn("w:color"), "3D5EE1")
    pBdr.append(bottom)
    pPr.append(pBdr)

    footer = section.footer
    footer.is_linked_to_previous = False
    fp = footer.paragraphs[0]
    fp.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    add_text(fp, "Santa Rosa Campus    ·    Page ", size=9, color=MUTED)
    fld_begin = OxmlElement("w:fldChar")
    fld_begin.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = " PAGE "
    fld_end = OxmlElement("w:fldChar")
    fld_end.set(qn("w:fldCharType"), "end")
    r1 = fp.add_run()
    r1._r.append(fld_begin)
    r2 = fp.add_run()
    r2._r.append(instr)
    run_font(r2, size=9, color=MUTED)
    r3 = fp.add_run()
    r3._r.append(fld_end)


def add_toc(doc):
    p = doc.add_paragraph()
    r1 = p.add_run()
    fc1 = OxmlElement("w:fldChar")
    fc1.set(qn("w:fldCharType"), "begin")
    r1._r.append(fc1)
    r2 = p.add_run()
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = r' TOC \o "1-2" \h \z \u '
    r2._r.append(instr)
    r3 = p.add_run()
    fc2 = OxmlElement("w:fldChar")
    fc2.set(qn("w:fldCharType"), "separate")
    r3._r.append(fc2)
    r4 = p.add_run("Right-click this line and choose Update Field to fill in the page numbers.")
    run_font(r4, size=11, italic=True, color=MUTED)
    r5 = p.add_run()
    fc3 = OxmlElement("w:fldChar")
    fc3.set(qn("w:fldCharType"), "end")
    r5._r.append(fc3)


def build():
    doc = Document()
    section = doc.sections[0]
    section.page_width = Inches(8.5)
    section.page_height = Inches(11)
    section.top_margin = Inches(0.9)
    section.bottom_margin = Inches(0.85)
    section.left_margin = Inches(0.9)
    section.right_margin = Inches(0.9)
    section.different_first_page_header_footer = True
    header_footer(section)

    normal = doc.styles["Normal"]
    normal.font.name = "Calibri"
    normal.font.size = Pt(11)
    normal.font.color.rgb = INK

    # Cover
    for _ in range(2):
        doc.add_paragraph()
    para(doc, "PANORAMA MONTESSORI SCHOOL, INC.", size=14, bold=True, color=BLUE, align="center", space_after=2)
    para(doc, "Santa Rosa Campus", size=12, color=MUTED, align="center", space_after=2)
    para(doc, "Panorama Ville, Barangay Dita, City of Santa Rosa, Laguna", size=11, color=MUTED, align="center", space_after=18)
    para(doc, "Learning Management System", size=28, bold=True, color=INK, align="center", space_after=2)
    para(doc, "User Manual", size=28, bold=True, color=BLUE, align="center", space_after=12)
    para(doc, "A complete guide for administrators, the registrar, teachers, students, and parents.", size=12, italic=True, color=MUTED, align="center", space_after=16)
    callout(
        doc,
        "Pictures are yours to add",
        "Every blue box in this manual is a picture place. Sign in as the person named in the box, open the screen described there, and paste your screenshot. The words stay as the caption.",
    )
    para(doc, "Document version 1.0", size=11, align="center", space_after=0)
    para(doc, "October 2026", size=11, color=MUTED, align="center", space_after=0)
    para(doc, "For school use. Passwords are never printed in this manual.", size=10, italic=True, color=MUTED, align="center")

    doc.add_page_break()

    h1(doc, "Contents")
    para(doc, "This list follows the chapter titles. After you open the file in Microsoft Word, right-click the line below and choose Update Field so the page numbers appear.", size=11)
    add_toc(doc)
    para(doc, "Chapters", size=12, bold=True, color=BLUE, space_before=12, space_after=4)
    for title in [
        "1. About this manual",
        "2. What the system is for",
        "3. People and what they can open",
        "4. Signing in, your profile, and your password",
        "5. Administrator guide",
        "6. Registrar guide",
        "7. Enrollment portal for families",
        "8. Teacher guide",
        "9. Student guide",
        "10. Parent guide",
        "11. Calendar, announcements, chat, and help",
        "12. Reports and downloadable documents",
        "13. Grades, attendance, and school-year rules",
        "14. When something does not work",
        "Appendix A. Screenshot checklist",
        "Appendix B. Descriptive grade scale",
    ]:
        para(doc, title, size=11, space_after=2)

    doc.add_page_break()

    h1(doc, "1. About this manual")
    para(doc, "This manual explains how to use the Panorama Montessori School portal from the first sign-in through enrollment, classes, lessons, quizzes, assignments, grades, attendance, and reports. It is written for the people who use the system every day.", align="justify")
    para(doc, "Read the chapter for your role. Administrators should also read the school-year rules in Chapter 13 before opening a new year. Teachers should read Chapter 8 before posting the first lesson. Families applying for a place should read Chapter 7.", align="justify")
    h2(doc, "1.1 How to add your pictures")
    steps(doc, [
        "Open this file in Microsoft Word.",
        "Find a blue box labeled INSERT YOUR SCREENSHOT HERE.",
        "Sign in to the portal as the person named in that box and open the screen it describes.",
        "Capture the screen. Include the left menu and the page title so a reader can tell where they are.",
        "Click inside the blue box, select the instruction text, and delete it.",
        "Choose Insert, then Pictures, then This Device, and select your image.",
        "Drag the corner of the picture so it fits the width of the page. Leave the Figure caption above the box.",
    ])
    callout(doc, "Picture quality", "Use a wide browser window. Hide personal passwords. If a list contains real student names, use a screen the school is allowed to publish in a printed manual.")
    h2(doc, "1.2 Words used in this manual")
    simple_table(doc, ["Word", "Meaning in this portal"], [
        ["Current academic year", "The one school year marked Current. Enrollment, classes, and new records use this year."],
        ["School year dates", "The first and last calendar days of that year, for example 3 January 2028 to 31 December 2029."],
        ["Enrollment period", "The days families may apply. These days must sit inside the school year dates."],
        ["Start of class / End of class", "The teaching dates. They are set after the enrollment period is saved."],
        ["Section", "A class group inside a grade, such as one Grade 7 section."],
        ["Subject", "A learning area taught in a grade, such as Filipino or Araling Panlipunan."],
        ["Lesson", "A teacher’s page for one subject and section. Activities and quizzes live inside a lesson."],
        ["Activity", "Work posted on a lesson: a quiz, practice exercise, project, or reflection."],
        ["Assignment", "A file-based task. Students upload the file type the teacher required."],
        ["Passing mark", "75 percent for numerical grades, Grade 4 through Grade 10."],
        ["School clock", "Philippine time. Deadlines and quiz start times follow this clock."],
    ])

    h1(doc, "2. What the system is for")
    para(doc, "The portal is the school’s learning and records system. It keeps the academic year, the people in the school, enrollment applications, class schedules, lessons, student work, grades, attendance, announcements, and official reports in one place.", align="justify")
    para(doc, "Records belong to a specific academic year. A class list, a grade, or an attendance mark for one year stays with that year. Opening an older year shows that year’s records. It does not copy them into the current year.", align="justify")
    bullets(doc, [
        "Families apply through the public Enrollment Portal when enrollment is open.",
        "The registrar reviews applications, documents, and enrollment dates.",
        "Administrators build the year, the classes, the subjects, the schedules, and the user accounts.",
        "Teachers post lessons, activities, quizzes, and assignments, mark attendance, and encode grades when they have been given that access.",
        "Students in Grade 7 through Grade 10 sign in to see their classes, work, and grades.",
        "Parents of Nursery through Grade 6 sign in for their children. Those students do not have a separate student login.",
        "Reports can be viewed on screen or downloaded.",
    ])
    figure(doc, "Portal home after sign-in", "The dashboard for an administrator, with the left menu open and the school name in the header.", "Administrator")

    h1(doc, "3. People and what they can open")
    para(doc, "The left menu changes with the account. A teacher never sees System Settings. A parent never sees Grade Entry. Use the account that matches the work.")
    simple_table(doc, ["Role", "Main menu", "Used for"], [
        ["Administrator", "Dashboard, User Management, Academic Management, Calendar, Analytics & Settings, Reports", "The whole school setup and oversight."],
        ["Registrar", "Dashboard, Enrollment Management, Chat, Reports", "Applications, enrollment dates, and enrollment reports."],
        ["Teacher", "Dashboard, Teaching & Learning, Assignments & Grades, Class Management, Calendar, Announcements, Reports", "Lessons, work, attendance, and grades for assigned classes."],
        ["Student", "Dashboard, My Classes, Calendar, Academic Records", "Grade 7–10 classwork, schedule, grades, and attendance."],
        ["Parent", "Dashboard, Calendar, My Children, Class Schedule, Attendance, Communication, Help", "Nursery–Grade 6 children, plus messages and procedures."],
    ])
    callout(doc, "Two kinds of student records", "Nursery through Grade 6 are managed from the parent account. Grade 7 through Grade 10 have their own student accounts. Creating a student login for Nursery through Grade 6 is blocked.")
    figure(doc, "Administrator menu", "The full left menu of an administrator, with User Management and Academic Management expanded.", "Administrator")
    figure(doc, "Teacher menu", "The teacher left menu, with Teaching & Learning and Assignments & Grades expanded.", "Teacher")
    figure(doc, "Student menu", "The student left menu, showing My Classes and Academic Records.", "Student in Grade 7, 8, 9, or 10")
    figure(doc, "Parent menu", "The parent left menu, showing My Children, Class Schedule, Attendance, and Communication.", "Parent")

    h1(doc, "4. Signing in, your profile, and your password")
    h2(doc, "4.1 Sign in")
    steps(doc, [
        "Open the school portal address given by the school.",
        "On the sign-in page, enter the email address of the account and the password.",
        "Choose the sign-in button.",
        "The dashboard opens. The left menu matches your role.",
    ])
    para(doc, "The sign-in page shows the school name, Panorama Montessori School INC., and the campus address. If the email or password is not accepted, check the spelling and the Caps Lock key, then use Forgot password.")
    figure(doc, "Sign-in page", "The full sign-in screen with the school logo, name, address, email field, and password field. Leave the password blank in the picture.", "Nobody. Stay signed out.")
    h2(doc, "4.2 Forgot password")
    steps(doc, [
        "On the sign-in page, open the forgot-password link.",
        "Enter the email address of the account and submit.",
        "Open the reset message and continue to the new-password page.",
        "Choose a new password, confirm it, and save.",
        "Return to the sign-in page and use the new password.",
    ])
    figure(doc, "Forgot password", "The password reset request page.", "Nobody. Stay signed out.")
    h2(doc, "4.3 Your profile and a new password")
    steps(doc, [
        "After signing in, open your name in the top bar.",
        "Open the profile page.",
        "Update the details the form allows, then save.",
        "To change the password while signed in, use the password section on the profile, enter the current password and the new password, and save.",
    ])
    figure(doc, "Profile page", "The signed-in user’s profile, with the name and email visible and the password fields empty.", "Any role")
    h2(doc, "4.4 Sign out")
    steps(doc, [
        "Open your name in the top bar.",
        "Choose Logout.",
        "The sign-in page returns. Close the browser if you are on a shared computer.",
    ])
    callout(doc, "Shared computers", "Always sign out before leaving a computer in a faculty room, a library, or a home that other people use.")

    h1(doc, "5. Administrator guide")
    para(doc, "The administrator prepares the school year before teachers and families use it. Do the work in this order: create the academic year, set the enrollment period, set the class dates, add semesters, confirm classes and subjects, assign teachers, build schedules, then open enrollment.")
    h2(doc, "5.1 Dashboard")
    para(doc, "The dashboard is the first page after sign-in. It summarizes the school. Use the left menu to open a specific area. The school name in the header is Panorama Montessori School Portal.")
    figure(doc, "Administrator dashboard", "The dashboard cards and the header, including the messages icon and the user menu.", "Administrator")

    h2(doc, "5.2 Academic years")
    para(doc, "Open Academic Management, then Academic Years. Each card is one school year. Filters let you show Current, Upcoming, Completed, or Archive.")
    h3(doc, "Add a year")
    steps(doc, [
        "Choose Add Academic Year.",
        "Enter the year name as a range, such as 2028-2029. The end year must be later than the start year.",
        "Enter the day the school year starts and a later day for when it ends.",
        "Save. A new year is created as Upcoming.",
    ])
    figure(doc, "Add Academic Year", "The add form with the year name and the start and end dates filled for a sample year.", "Administrator")
    h3(doc, "Open a year")
    steps(doc, [
        "Choose the year card.",
        "The detail window shows status, enrollment, semester count, school year starts, and school year ends.",
        "Use Edit if the name or the school-year dates must change.",
        "Set the status to Current only for the year the school is running now. Keep other years Upcoming or Completed.",
    ])
    callout(doc, "One current year", "Enrollment settings can be changed only on the year marked Current. Teachers and reports follow that current year. Looking at an older year does not move its classes into the new year.")
    figure(doc, "Academic year details", "The detail window of the current year, showing status, school year starts, school year ends, and enrollment closed or open.", "Administrator")
    h3(doc, "Enrollment period")
    para(doc, "The enrollment period is the range of days when families may apply. Both dates must fall inside the school year already saved. The calendar offers only those years. Earlier years do not appear in the year list, and days outside the school year cannot be chosen.")
    steps(doc, [
        "On the current year, find Enrollment Period.",
        "Set Start Date and End Date inside the school year.",
        "Choose Save Enrollment Dates.",
        "After the dates are saved, Start of class and End of class appear.",
        "Choose Open Enrollment only when the school is ready to receive applications. Close Enrollment when the period should stop.",
    ])
    figure(doc, "Enrollment period calendar", "The enrollment start or end calendar opened on the current year. The year list should show only the years inside that school year.", "Administrator")
    h3(doc, "Start of class and end of class")
    para(doc, "Class dates come after the enrollment period. The suggested start is the next weekday after enrollment ends. If the day after enrollment is a Saturday or Sunday, the suggestion moves to Monday. The suggested end is the last day of the school year. If that day is a Saturday or Sunday, the suggestion moves to the weekday before it.")
    steps(doc, [
        "Read the suggested dates under the class-date fields.",
        "Keep the suggestion or choose other weekdays inside the school year and after the enrollment end date.",
        "Start of class must be after the enrollment period ends.",
        "End of class must be on or before the last day of the school year.",
        "Choose Save class dates.",
    ])
    figure(doc, "Suggested class dates", "The Start of class and End of class fields with the suggestion sentences visible. Show a case where the suggestion is a weekday.", "Administrator")
    h3(doc, "Archive or delete a year")
    para(doc, "Archive keeps the year on record, together with its semesters and class schedules. Classes, subjects, and curriculum stay. The current year cannot be archived. Unarchive brings an archived year back. Delete removes the year. Use Archive when the school still needs the history.")
    figure(doc, "Archive and Delete", "The bottom of the year window showing Edit, Open Enrollment or Close Enrollment, Archive, and Delete.", "Administrator")

    h2(doc, "5.3 Semesters")
    para(doc, "Open Academic Management, then Semesters, or choose Manage Semesters from the year window. Add the semesters that belong to the current year. Class schedules and some records use the semester you select, so create the semester before building the weekly timetable.")
    figure(doc, "Semesters", "The semester list for the current academic year.", "Administrator")

    h2(doc, "5.4 Classes and subjects")
    para(doc, "Open Academic Management, then Classes & Subjects. This is where grade levels, sections, and subjects are kept together. A subject belongs to a grade. A section is the group of students in that grade. Teachers are assigned to a subject and section for the current year.")
    steps(doc, [
        "Select the academic year you are preparing.",
        "Confirm the grade levels the school offers, from Nursery through Grade 10.",
        "Add or confirm the section for each grade.",
        "Add or confirm the subjects for that grade. Use the subject name the school uses, such as Araling Panlipunan or Filipino.",
        "Assign a teacher to each subject and section for this year.",
        "Save. The teacher then sees that class under My Classes & Subjects.",
    ])
    callout(doc, "Keep master records", "Do not create a second teacher account or a second copy of a subject for a new year. Assign the existing teacher and the existing subject to the new year.")
    figure(doc, "Classes and subjects", "The Classes & Subjects page with a grade, its sections, its subjects, and a teacher assignment visible.", "Administrator")

    h2(doc, "5.5 Curriculum")
    para(doc, "Open Academic Management, then Curriculum. The curriculum lists the subjects offered for each grade. Course outlines that teachers later post are checked against this list in the curriculum reports.")
    figure(doc, "Curriculum", "The curriculum page for one grade, showing its subjects.", "Administrator")

    h2(doc, "5.6 Class schedules")
    para(doc, "Open Academic Management, then Class Schedules. A schedule row is one meeting: teacher, subject, section, day, time, and room for the current year and semester.")
    steps(doc, [
        "Choose the academic year and the semester.",
        "Add a meeting. Select the teacher, subject, section, day, start time, end time, and room.",
        "Save and review the weekly view.",
        "If two meetings use the same teacher or the same room at the same time, the Schedule Conflict report will list them. Adjust one of the meetings.",
    ])
    para(doc, "Teachers open the same meetings under My Schedule. Students in Grade 7 through Grade 10 open them under My Schedule. Parents open the child’s meetings under Class Schedule.")
    figure(doc, "Class schedule", "The weekly schedule with at least one complete meeting: subject, section, teacher, day, and time.", "Administrator")

    h2(doc, "5.7 User management")
    para(doc, "Open User Management. All Users lists every account. Create User starts a new account. Activity Log shows what was done in the portal.")
    steps(doc, [
        "Open All Users to search for an account.",
        "Open a user to review the role, the email, and the status.",
        "Use Create User when a new administrator, registrar, teacher, student, or parent account is required.",
        "Give the person the email and the temporary password through a private channel. Ask them to change the password after the first sign-in.",
    ])
    figure(doc, "All users", "The user list with the role column visible. Hide or crop any password field.", "Administrator")
    h3(doc, "Teachers")
    para(doc, "Open User Management, then Teachers. Add a teacher once. The teacher profile stays. Each year you assign that teacher to classes. You do not create a new teacher account for the same person every school year.")
    para(doc, "If a teacher leaves, use the deactivation and transfer steps so their classes can move to another teacher. Deactivate the account after the classes have a new teacher.")
    figure(doc, "Teacher list", "The teacher list and the button used to add a teacher.", "Administrator")
    h3(doc, "Students")
    para(doc, "Open User Management, then Students. Student profiles stay on record. Placement in a section is year by year.")
    para(doc, "For Grade 7 through Grade 10, the student has a login. For Nursery through Grade 6, the parent account is the login. The system will not create a separate student login for those grades.")
    figure(doc, "Student list", "The student list filtered or scrolled so both a lower-grade student and a Grade 7 student can be seen if your data has them.", "Administrator")
    h3(doc, "Parents")
    para(doc, "Open User Management, then Parents. Link each child to the parent. A parent can have more than one child. After sign-in, the parent opens My Children and chooses the child.")
    figure(doc, "Parent list", "The parent list, or one parent record showing the linked child.", "Administrator")
    h3(doc, "Enrollments inside the school")
    para(doc, "Open User Management, then Enrollments. This list is the placement of students in subjects for a year. It is separate from the public application form. An approved application becomes a student placement. The enrollment list is where you confirm that the student is in the right subject, section, and year.")
    figure(doc, "Enrollment list", "The enrollment list for the current year, with subject and section columns visible.", "Administrator")

    h2(doc, "5.8 Grade encoding access")
    para(doc, "Open User Management, then Grade Encoding Access. A teacher can post lessons without this access. Encoding quarterly or term grades is allowed only after an administrator grants it. New teachers do not receive it automatically. Grant it to the teachers who are responsible for official grades, and remove it when that duty ends.")
    figure(doc, "Grade encoding access", "The access page showing at least one teacher granted and one teacher without access, if both exist.", "Administrator")

    h2(doc, "5.9 Student promotions")
    para(doc, "Open Academic Management, then Student Promotions. At the end of the year, promote students to the next grade, record who will repeat, and record who withdrew or transferred. History keeps the earlier placement. Promoting a student does not delete the previous year’s grades.")
    steps(doc, [
        "Open Student Promotions and choose Create, or the equivalent new-promotion action.",
        "Select the student, the year they are leaving, and the next grade or the leaving status.",
        "Save.",
        "Open History to review what was recorded.",
    ])
    figure(doc, "Student promotions", "The promotions page with the grade choices from Nursery through Grade 10.", "Administrator")

    h2(doc, "5.10 Announcements, calendar, and analytics")
    para(doc, "Announcements are under Analytics & Settings. Post a school-wide notice or a notice for a role. Teachers also post announcements for their own sections from the teacher menu.")
    para(doc, "Calendar & Events holds school events. Create an event with a start and an end. An ordinary event can cover up to three days. People see the same calendar from their own menu.")
    para(doc, "School Analytics, Performance Hub, and Grading Progress show how the current year is moving: enrollment, performance, and which classes still need grades. These pages read the current year. An empty result means the current year has no rows yet.")
    figure(doc, "Announcements", "The announcement list and the button used to post a new announcement.", "Administrator")
    figure(doc, "School calendar", "The month view with at least one school event.", "Administrator")
    figure(doc, "Grading progress", "The grading progress page for the current year.", "Administrator")

    h2(doc, "5.11 System settings and backup")
    para(doc, "Open Analytics & Settings, then System Settings, for the school identity files and limits the settings page controls. Open Backup & Recovery to create a backup, download it, or remove an old backup file. Keep downloaded backups outside the server as well.")
    steps(doc, [
        "Open Backup & Recovery.",
        "Create a backup before a promotion run, a year change, or a large import.",
        "Download the file and store it in the school’s backup location.",
        "Delete a backup file from the server only after the copy has been stored.",
    ])
    figure(doc, "Backup and recovery", "The backup page with the create and download actions visible.", "Administrator")
    h2(doc, "5.12 Activity log")
    para(doc, "Open User Management, then Activity Log. Use it when you need to see who changed an account or when an important action was recorded. It supports the audit reports in Chapter 12.")
    figure(doc, "Activity log", "The activity log with several recent rows visible. Crop anything that shows a password.", "Administrator")

    h1(doc, "6. Registrar guide")
    para(doc, "The registrar runs enrollment. The menu is Dashboard, Enrollment Management, Chat, and Reports.")
    h2(doc, "6.1 Prepare the enrollment period")
    para(doc, "On Applications, the readiness panel shows whether the current year is ready. Set the enrollment start date, the enrollment end date, and the enrollment status, then save. The dates must sit inside the current school year, the same rule the administrator follows. Families can start an application only while enrollment is open and today’s date is inside the period.")
    figure(doc, "Registrar enrollment settings", "The top of the Applications page with academic year, enrollment status, start date, and end date.", "Registrar")
    h2(doc, "6.2 Review an application")
    para(doc, "Applications arrive as Pending. Status filters include draft, pending, under review, approved, rejected, and needs documents. Sort and search from the filter row.")
    steps(doc, [
        "Open Enrollment Management, then Applications.",
        "Find the applicant. Note the application number, the grade, and the status.",
        "Choose Actions, then View Details.",
        "Read the form and the uploaded documents.",
        "If the file is still being checked, choose Mark Under Review while the status is Pending.",
        "When the record is complete and the school accepts the student, choose Approve while the status is Under Review.",
        "If documents are missing, use the document actions on the detail page so the family can see what is still required.",
    ])
    para(doc, "Approve is available when the application is under review. Nursery through Grade 6 stay on the parent account. Grade 7 through Grade 10 can receive a student account as part of acceptance. Delete is permanent. Use it only for a record the school truly wants removed. The Archive page can restore applications that were removed from the active list.")
    figure(doc, "Application list", "The applications table with status chips and the Actions menu open on one row.", "Registrar")
    figure(doc, "Application details", "One application open, showing the student or parent name, the grade, and the documents.", "Registrar")
    h2(doc, "6.3 Statistics, archive, and the public portal")
    para(doc, "Statistics charts pending, under review, approved, rejected, and needs-documents counts. Archive lists applications that left the active queue, grouped by their final status, and can restore them. Portal View opens the public enrollment site in a new tab so you can see what families see.")
    figure(doc, "Enrollment statistics", "The statistics page with the status chart.", "Registrar")
    h2(doc, "6.4 Registrar reports and chat")
    para(doc, "Reports & Documents opens the same report library, limited to reports the registrar may view. Enrollment summaries, pending applications, and document checklists are the daily ones. Chat is the in-portal message tool.")
    figure(doc, "Registrar reports", "The reports page as the registrar sees it.", "Registrar")

    h1(doc, "7. Enrollment portal for families")
    para(doc, "Families do not need a staff account to apply. They open the Enrollment Portal. The welcome page shows the current school year. If enrollment is closed, the page says so and the start buttons stay unavailable. Track Application still works.")
    h2(doc, "7.1 New student")
    para(doc, "Choose New Student when the child is enrolling at Panorama Montessori School for the first time. The form collects the learner, the parent or guardian, the grade being applied for, and the required documents. A new student does not submit an SF9. After a complete application, the school assigns a section.")
    steps(doc, [
        "Open the Enrollment Portal.",
        "Choose Start Application or Start New Student Enrollment.",
        "Complete every required section. Fields marked required must be filled before the form will save.",
        "Upload the documents the form asks for. Use clear scans or photos.",
        "Read the agreement to follow the rules of Panorama Montessori School, Inc., and confirm it.",
        "Submit. Keep the application number.",
        "Use Track Application later. Enter the details the status page asks for.",
    ])
    figure(doc, "Enrollment welcome", "The public welcome page showing New Student and Old Student, taken while enrollment is open.", "Nobody. Use the public portal.")
    figure(doc, "New student form", "A middle section of the new-student form showing required fields. Use sample data or cover private numbers if this manual will be shared.", "Nobody. Use the public portal.")
    h2(doc, "7.2 Old student")
    para(doc, "Choose Old Student when the learner is returning. The family signs in with the old-student login, reviews subjects and the schedule, chooses the block section, and generates the enrollment form.")
    steps(doc, [
        "On the welcome page, choose Login for Old Students.",
        "Sign in with the returning-student credentials the school issued.",
        "Review the subjects and schedule for the new year.",
        "Choose the block section.",
        "Generate the enrollment form and submit or print it as the page instructs.",
    ])
    figure(doc, "Old student enrollment", "The old-student page after login, showing subjects or the section choice.", "Old-student login")
    h2(doc, "7.3 Track an application")
    steps(doc, [
        "On the welcome page, choose Track Application.",
        "Enter the application number and the other detail the form requires.",
        "Read the status. Pending and Under Review mean the registrar is still working. Approved means the school accepted the application. Needs Documents means a file must be replaced or added.",
    ])
    figure(doc, "Track application", "The status lookup page, or a result page with the status chip visible.", "Nobody, or an applicant who knows their application number.")

    h1(doc, "8. Teacher guide")
    para(doc, "A teacher sees only the classes assigned for the year they are working in. My Classes & Subjects lists those assignments with the subject name and the section. If a class is missing, ask the administrator to assign it. Do not create a second account.")
    h2(doc, "8.1 Dashboard and schedule")
    para(doc, "The dashboard opens after sign-in. My Schedule, under Teaching & Learning, shows the weekly meetings the administrator saved.")
    figure(doc, "Teacher dashboard", "The teacher dashboard.", "Teacher")
    figure(doc, "My Schedule", "The teacher weekly schedule.", "Teacher")
    h2(doc, "8.2 Lessons")
    para(doc, "Open Teaching & Learning, then My Lessons. A lesson is the home of one class. Create the lesson for the subject and section you teach. Inside it, post activities.")
    steps(doc, [
        "Open My Lessons.",
        "Create a lesson. Select the subject and the section.",
        "Save, then open the lesson.",
        "Add an activity from the lesson. The newest activity appears at the top of the list for you and for the student.",
    ])
    figure(doc, "My Lessons", "The lesson list for the teacher.", "Teacher")
    h3(doc, "Activity types")
    para(doc, "When you add an activity, choose one type:")
    bullets(doc, [
        "Quiz — questions with answer keys. The portal grades multiple choice and true or false.",
        "Practice Exercise — work that reinforces a skill.",
        "Project — a larger task with instructions, a required output, a deadline, and how it will be judged.",
        "Reflection — a written response about what the student learned.",
    ])
    para(doc, "Nursery through Grade 3 do not use a point score on activities. Grade 4 through Grade 10 can use points. A posted activity can later be closed. Closing does not ask a second question: the activity closes as soon as you choose Close. The label becomes Closed. Reopen puts it back to Posted. Students can still open a closed activity and can still see results they already earned. They cannot start or submit after it is closed.")
    figure(doc, "Lesson with activities", "A lesson page listing activities, newest at the top, with a Posted or Closed label visible.", "Teacher")
    figure(doc, "Activity page", "One posted activity with its title, subject, grade, due date, points or instructions, and the Close action.", "Teacher")
    h3(doc, "Build a quiz")
    steps(doc, [
        "Add an activity and choose Quiz.",
        "Enter the title, the instructions, the start date and time, the due date and time, and the attempt settings the form shows.",
        "Add questions. Use multiple choice or true or false.",
        "Mark the correct answer and the points for each question.",
        "Set the passing percentage. The school passing mark for numerical work is 75 percent.",
        "Save, preview, then post.",
    ])
    para(doc, "The start time is the time you type, on the Philippine school clock. Students may open the quiz page before the start time, but they cannot begin until that time. The due time is also Philippine time. A quiz score stays on the quiz. It does not write itself into the quarterly grade. Enter official grades separately in Grade Entry if you have been given that access.")
    figure(doc, "Quiz builder", "The quiz form with one multiple-choice question and the answer key visible to the teacher.", "Teacher")
    figure(doc, "Quiz results", "The teacher results page with at least one student attempt, the score, and the review action.", "Teacher")
    h3(doc, "Close an activity")
    steps(doc, [
        "Open the lesson and find the posted activity.",
        "Choose Close. The activity closes immediately.",
        "The status chip reads Closed.",
        "Choose Reopen later if students must submit again.",
    ])
    h2(doc, "8.3 Assignments")
    para(doc, "Open Assignments & Grades, then All Assignments. An assignment asks the student to upload a file. You choose the subject, the section, the due date and time, the points, and the file types you will accept.")
    steps(doc, [
        "Create an assignment for one of your subjects and sections.",
        "Set the due date and due time.",
        "Tick only the file types you want. If the class should submit PDF only, leave the other types unticked and save.",
        "Post the assignment.",
        "Open Grade Submissions to see who submitted and who is still missing. The missing list is the students enrolled in that subject and section for that year.",
        "Open a submission to view the file on the page, enter the score and feedback, and save.",
    ])
    para(doc, "The subject name on the submissions page comes from the subject record. A Grade 7 Araling Panlipunan assignment shows that subject, not a blank label. Students who have not submitted stay on the missing list until they upload a file.")
    figure(doc, "Assignment form", "The create or edit assignment form with subject, section, due date, and allowed file types.", "Teacher")
    figure(doc, "Grade submissions", "The submissions page showing the subject, the section, submitted work, and the students who have not submitted.", "Teacher")
    h2(doc, "8.4 Grade entry and performance")
    para(doc, "Grade Entry appears only when an administrator has granted grade-encoding access. Open it to encode the official term grades for your class. Performance Hub summarizes how the class is doing.")
    para(doc, "Nursery through Grade 3 use descriptive letters, not numbers. Grade 4 through Grade 10 use numerical scores. A general average below 75 percent is below the passing mark.")
    figure(doc, "Grade entry", "The grade entry sheet for one class. If your account has no Grade Entry menu, photograph the Performance Hub instead and note that access has not been granted.", "Teacher with grade-encoding access")
    h2(doc, "8.5 Attendance")
    para(doc, "Open Class Management, then Attendance. Choose the class and the date, then mark each student Present, Late, Absent, or Excused, and save. The date follows the school calendar. Parents and students see the marks that were saved.")
    figure(doc, "Attendance sheet", "One class attendance day with the marks visible.", "Teacher")
    h2(doc, "8.6 Posts and consultations")
    para(doc, "All Posts is the class stream for short updates. Consultations lists parent–teacher meeting requests. Open a request, set a time if you accept it, and mark it completed when the meeting is done.")
    figure(doc, "Consultations", "The teacher consultation list.", "Teacher")
    h2(doc, "8.7 Teacher announcements and reports")
    para(doc, "Announcements lets you post to the sections you teach. Reports & Documents lets you generate a student transcript, a class list, a grade slip, or a progress report, and open the learning and attendance reports that teachers may view. Pick the grade level first, then the section, then one student or the entire section.")
    figure(doc, "Teacher reports", "The documents row: Student Transcript, Class List, Grade Slip, and Progress Report.", "Teacher")

    h1(doc, "9. Student guide")
    para(doc, "This chapter is for Grade 7 through Grade 10. Nursery through Grade 6 students use the parent account described in Chapter 10.")
    h2(doc, "9.1 Dashboard and classes")
    para(doc, "After sign-in, the dashboard opens. My Classes lists each subject you are enrolled in for the current year. Open a subject to see its lessons and activities. The newest activity is at the top.")
    figure(doc, "Student classes", "My Classes expanded, or one subject opened.", "Student")
    h2(doc, "9.2 Open a lesson and take a quiz")
    steps(doc, [
        "Open the subject, then the lesson, then the quiz.",
        "Read the instructions. If the start time has not arrived, wait. The page may be visible before you are allowed to begin.",
        "Start the attempt when the button is available.",
        "Answer each question. The page shows how many items you have answered. Your answers are kept while you move between questions.",
        "Choose Submit. A window tells you how many items are answered and how many are blank.",
        "Choose Keep working to go back, or Submit quiz to finish.",
        "After the last moment of the time limit, the quiz submits itself.",
    ])
    para(doc, "You cannot change answers after you submit. If the teacher has closed the quiz, you can still read it and open a result you already have. You cannot start a new attempt.")
    figure(doc, "Student quiz", "The quiz-taking page with the question, the choices, and the progress area. Use a practice quiz if you do not want a real score in the picture.", "Student")
    figure(doc, "Submit quiz window", "The submit window showing the answered count and the buttons Keep working and Submit quiz.", "Student")
    h2(doc, "9.3 Assignments and uploaded files")
    steps(doc, [
        "Open the assignment from the subject or the assignments list.",
        "Read the allowed file type and the due date.",
        "Choose the file. If the type is not allowed, a warning tells you to submit the required file. The wrong file is not kept.",
        "The file you chose appears under the assignment details. A PDF is shown on the page.",
        "If you chose the wrong file before submitting, remove it and choose another.",
        "Submit.",
        "After submitting, the file remains on the page. You can remove it and upload a different file only while the assignment is still open and the teacher has not graded it.",
    ])
    figure(doc, "Student assignment with file", "The assignment page with a PDF shown under the assignment details.", "Student")
    figure(doc, "File not allowed", "The warning window that appears when the file type is not the one the teacher required.", "Student")
    h2(doc, "9.4 Schedule, grades, attendance, and analytics")
    para(doc, "Under Academic Records, open My Schedule for the weekly timetable, Grades for encoded grades and the report card the school has released, and Attendance for your marks. My Analytics summarizes your performance for the current year. Consultations is where you or your family can see meeting requests if your school uses them from the student account. Announcements lists notices addressed to you.")
    figure(doc, "Student grades", "The grades page for the current year.", "Student")
    figure(doc, "Student attendance", "The student attendance history.", "Student")

    h1(doc, "10. Parent guide")
    para(doc, "Parents sign in with the parent account. My Children lists every linked learner. Nursery through Grade 6 are viewed here. There is no separate student password for those grades.")
    steps(doc, [
        "Sign in.",
        "Open My Children and choose the child.",
        "Open Class Schedule for that child’s meetings.",
        "Open Attendance for present, late, absent, and excused marks.",
        "Open Announcements and Chat under Communication.",
        "Open Office Procedures for the school’s listed office steps.",
        "Open Help for the in-portal help page.",
    ])
    para(doc, "Grades and report cards for the child are opened from the child’s record when the school has published them. A parent sees the current year’s records for that child.")
    figure(doc, "My Children", "The parent menu with at least one child named.", "Parent")
    figure(doc, "Parent view of attendance", "Attendance for one child.", "Parent")
    figure(doc, "Office procedures", "The Office Procedures page.", "Parent or Student")

    h1(doc, "11. Calendar, announcements, chat, and help")
    h2(doc, "11.1 Calendar")
    para(doc, "Every role that has Calendar & Events can open the month view. Administrators and staff who are allowed to add events create the event, set whether it lasts all day, and set the start and end. A timed event uses the school clock. A normal event may span up to three days.")
    h2(doc, "11.2 Announcements")
    para(doc, "Announcements are notices. An administrator can post to the school. A teacher can post to a class. Students and parents read the notices addressed to them. Old filters that used to hide announcements have been removed, so the list shows the notices for your account.")
    h2(doc, "11.3 Chat")
    para(doc, "The messages icon in the header opens Chat. The registrar and parents also have Chat in the menu. Use it for school messages. It is not a private social network. Sign out when you leave a shared computer so the next person cannot read the thread.")
    figure(doc, "Chat", "The message list. Crop private conversation text if the manual will be printed for a wide audience.", "Any role that can open Chat")
    h2(doc, "11.4 Help")
    para(doc, "Help is in the parent menu and on the help page for signed-in users. Use it for short reminders. This manual is the full reference.")

    h1(doc, "12. Reports and downloadable documents")
    para(doc, "Open Reports & Documents. A search box finds a report by name. Each report can be viewed and downloaded. The file follows the current academic year. If the year has no grades yet, the report is empty for that year.")
    h2(doc, "12.1 Documents the school generates")
    para(doc, "Administrators and teachers see four generate cards. Choose the grade level first, then the section, then one student or Entire section.")
    bullets(doc, [
        "Student Transcript — academic history for one student, or one PDF of every enrolled student in the section.",
        "Class List — the section roster.",
        "Grade Slip — period grades for one student or the whole section.",
        "Progress Report — a performance summary for one student or the whole section.",
    ])
    para(doc, "Nursery and Kindergarten documents use descriptive letters. Grade 4 through Grade 10 documents use numerical scores. Grade 1 through Grade 3 also use the descriptive scale when grades are encoded.")
    figure(doc, "Generate a document", "The download window for a transcript or a grade slip, with grade level and section selected.", "Administrator or Teacher")
    h2(doc, "12.2 Report library")
    para(doc, "The rest of the page is grouped. Open View Report to read it, or Download to save the PDF. You only see groups your role is allowed to open.")
    simple_table(doc, ["Group", "Use it when you need"], [
        ["Enrollment and Student Reports", "Counts by year and grade, the master list, new and returning students, pending applications, section placement, incomplete requirements, and withdrawals."],
        ["Academic and Grading Reports", "Report cards, grade slips, class sheets, ranking, failing or missing grades, descriptive assessments, and grade-change history."],
        ["Attendance Reports", "Daily and monthly marks, student history, excessive absences, and corrections."],
        ["Teacher, Subject, and Scheduling Reports", "Teaching load, subject assignment, the class schedule, conflicts, and subjects that still have no teacher."],
        ["Lessons, Activities, and Learning Reports", "Lessons posted, submission status, missing work, and upcoming deadlines."],
        ["Communication and Notification Reports", "Announcements, consultations, and calendar events."],
        ["Student Documents and Records", "Checklists, missing files, and documents waiting for verification."],
        ["Principal and Administrative Reports", "School-wide enrollment, performance, students below 75 percent, and workload."],
        ["System and User Activity Reports", "Accounts, sign-in history, and audit trails. Passwords are never included."],
    ])
    figure(doc, "Report library", "Two report groups expanded, with View Report and Download visible.", "Administrator")

    h1(doc, "13. Grades, attendance, and school-year rules")
    h2(doc, "13.1 Numerical grades")
    para(doc, "Grade 4 through Grade 10 use numbers. The passing mark is 75 percent. A general average below 75 percent appears on the failing-grades and academic-risk reports. Quiz scores and assignment scores are classwork. They stay on the quiz or the assignment until a teacher with grade-encoding access enters the official term grade.")
    h2(doc, "13.2 Descriptive grades")
    para(doc, "Nursery through Grade 3 use letters A to E. The portal will not accept a numerical grade for those levels, and activities in those grades do not ask for a point score. The meaning of each letter is in Appendix B.")
    h2(doc, "13.3 Attendance marks")
    para(doc, "Teachers save Present, Late, Absent, or Excused. Attendance percentage counts present and late marks. Excused marks are left out of the rate. The excessive-absences report lists students with five or more absences in the current academic year. Corrections are kept in the attendance history.")
    h2(doc, "13.4 The school year is a boundary")
    bullets(doc, [
        "Create the school year start and end before you set the enrollment period.",
        "Enrollment dates must fall on or between those two days. The calendar hides every other year.",
        "Class dates come after the enrollment period is saved.",
        "Suggested class dates skip Saturday and Sunday.",
        "Only the year marked Current can open enrollment.",
        "A profile is permanent. A class list, a grade, and an attendance mark belong to one year.",
        "An empty current year is empty. The portal does not fill it with last year’s rows.",
        "Archive a year to keep its history. Delete only when the school intends to remove that year.",
    ])
    h2(doc, "13.5 Time")
    para(doc, "Deadlines, quiz start times, and attendance “today” use Philippine time, even if the server clock is different. Type the time you mean on the wall clock in Santa Rosa. A student cannot start a quiz before that time and cannot submit an activity after the teacher has closed it.")

    h1(doc, "14. When something does not work")
    simple_table(doc, ["What you see", "What to do"], [
        ["The sign-in is rejected.", "Check the email and Caps Lock. Use Forgot password. Ask an administrator to confirm the account is active."],
        ["A teacher has no class.", "The administrator assigns the existing teacher to the subject and section for this year."],
        ["Grade Entry is missing.", "The administrator has not granted grade-encoding access. Lessons can still be posted."],
        ["The enrollment calendar shows old years.", "Open the year and confirm the school year start and end are saved. The enrollment dates then stay inside that range."],
        ["A suggested class date falls on Saturday or Sunday.", "Refresh the year window. The suggestion moves to the next weekday, or to the weekday before the school year ends."],
        ["Families cannot start an application.", "Enrollment must be open, and today’s date must be inside the enrollment period of the current year."],
        ["A student in Nursery to Grade 6 cannot sign in.", "That is expected. The parent signs in."],
        ["A quiz will not start.", "The start time has not arrived, the attempts are used up, or the teacher closed it."],
        ["An assignment file is rejected.", "The teacher limited the file type or the size. Choose the required file. PDF is required when only PDF is ticked."],
        ["A submitted file cannot be removed.", "The teacher already graded it, or the assignment is closed."],
        ["Subject shows as blank on submissions.", "The assignment must be saved with a real subject. Reopen the assignment, choose the subject and section, and save."],
        ["A report is empty.", "The current year has no rows for that report yet. Check that you are not expecting last year’s data."],
        ["Two classes clash.", "Open the Schedule Conflict report and change the teacher, the room, or the time."],
        ["The menu looks wrong.", "You are signed in as a different role. Sign out and sign in with the correct account."],
    ])
    para(doc, "If the step is still blocked, note the page title, the role, the academic year on the screen, and the time on the school clock, then tell the administrator. Do not send a password in the message.", space_before=6)

    h1(doc, "Appendix A. Screenshot checklist")
    para(doc, "Take the pictures in this order if you want the manual to match the figure numbers. You can photograph them in any order. Paste each one into the box with the same figure number.")
    simple_table(
        doc,
        ["Figure", "Title", "Sign in as", "Show this"],
        [[str(n), title, who, what] for n, title, what, who in FIGURES],
    )

    h1(doc, "Appendix B. Descriptive grade scale")
    para(doc, "Use this scale for Nursery through Grade 3.")
    simple_table(doc, ["Letter", "English", "Filipino", "Meaning"], [
        ["A", "Advancing", "Namumukod-tangi", "Demonstrates skills beyond grade-level expectations, with confidence, accuracy, and high quality."],
        ["B", "Benchmarking", "Napamamalas", "Demonstrates skills at the expected grade level. The work is accurate and meets the standard."],
        ["C", "Connecting", "Natutungo", "Approaching grade-level expectations. Some tasks still need guidance."],
        ["D", "Developing", "Napauunlad", "Skills are emerging. The learner needs continued practice and support."],
        ["E", "Emerging", "Nagsisimula", "Beginning to demonstrate skills and needs ongoing guided instruction."],
    ])
    para(doc, "Grade 4 through Grade 10 use numerical scores. The passing mark is 75 percent.", space_before=8)
    para(doc, "— End of the user manual —", size=11, italic=True, color=MUTED, align="center", space_before=18)

    core = doc.core_properties
    core.title = "Panorama Montessori School LMS User Manual"
    core.subject = "User manual for the Panorama Montessori School learning portal"
    core.category = "User manual"
    core.comments = "Screenshot boxes are left empty for the school to insert."

    settings = doc.settings.element
    update = OxmlElement("w:updateFields")
    update.set(qn("w:val"), "true")
    settings.append(update)

    doc.save(OUT)
    print(f"Saved {OUT}")
    print(f"Figures: {len(FIGURES)}")


if __name__ == "__main__":
    build()
