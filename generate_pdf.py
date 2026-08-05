import json
from fpdf import FPDF

class PDF(FPDF):
    def header(self):
        self.set_font('Arial', 'B', 15)
        self.cell(0, 10, 'PSG iTech - Faculty Credentials', 0, 1, 'C')
        self.ln(5)

data = json.load(open('storage/app/faculty_credentials.json'))

pdf = PDF()
pdf.add_page()
pdf.set_font("Arial", size=10)

# Table Header
pdf.set_font("Arial", 'B', 10)
pdf.cell(50, 10, 'Name', 1)
pdf.cell(85, 10, 'Email', 1)
pdf.cell(50, 10, 'Password', 1)
pdf.ln()

# Table Data
pdf.set_font("Arial", size=9)
for row in data:
    pdf.cell(50, 8, row['name'], 1)
    pdf.cell(85, 8, row['email'], 1)
    pdf.cell(50, 8, row['password'], 1)
    pdf.ln()

pdf.output("public/faculty_credentials.pdf")
print("Done")
