# Quiz & Assessment Analytics for Moodle (`report_quizanalytics`)

An advanced, course-level analytics and reporting plugin for Moodle 5.0+ (and Moodle 4.x), providing teachers and academic managers with actionable visual insights into quiz performance, attempt trends, question difficulty, and customizable Excel/CSV exports.

---

## 🌟 Key Features

- **📊 Comprehensive Summary Dashboard:**
  - Total student attempts & unique student counts.
  - Class average score, median, highest, and lowest scores.
  - Overall passing percentage and duration per attempt.

- **📈 Score Distribution Analysis:**
  - Visual tiered breakdown of student results:
    - 85% – 100% (Excellent)
    - 70% – 84% (Good)
    - 50% – 69% (Average)
    - Below 50% (Needs Support / Intervention)

- **🎯 Question Difficulty & Facility Index:**
  - Identifies which specific quiz questions students struggled with the most.
  - Computes facility index (% of correct answers) per question slot.

- **👥 Detailed Student Performance Table:**
  - View individual student attempts, durations, final grades, and pass/fail statuses.

- **📥 Customizable Multi-Format Export:**
  - Export directly to **Microsoft Excel (.xlsx)** or **CSV (.csv)**.
  - **Custom Field Picker**: Select or deselect columns before downloading (Student Name, Email, ID Number, Department, Attempts, Timestamps, Duration, Grade, %, Pass/Fail).

---

## 📋 Requirements

- **Moodle Version:** Moodle 4.5+ or Moodle 5.0+
- **PHP Version:** PHP 8.1 / 8.2 / 8.3+
- **Activity:** Standard Moodle `mod_quiz` assessments

---

## 🚀 Installation

### Method 1: Git Clone
1. Navigate to your Moodle directory:
   ```bash
   cd /path/to/moodle/report
   ```
2. Clone this repository as `quizanalytics`:
   ```bash
   git clone https://github.com/jchanthy/moodle-report_quizanalytics.git quizanalytics
   ```
3. Visit **Site Administration > Notifications** in your browser to complete the database upgrade.

### Method 2: ZIP Upload
1. Download the latest release `.zip` package.
2. Log into Moodle as an Administrator.
3. Go to **Site Administration > Plugins > Install plugins**.
4. Upload the ZIP file and proceed with the installation prompt.

---

## 🔒 Capabilities & Permissions

- `report/quizanalytics:view`: Allows users to view quiz analytics and export reports.
  - **Default Roles Allowed:** Teacher, Non-editing teacher, Manager, Administrator.

---

## 📁 Directory Structure

```
report_quizanalytics/
├── amd/
│   └── src/
│       └── charts.js               # Frontend UI helper
├── classes/
│   ├── output/
│   │   ├── main_page.php           # Renderable & Templatable class
│   │   └── renderer.php            # Custom output renderer
│   └── quiz_analyzer.php           # Core calculations & DB aggregations
├── db/
│   └── access.php                  # Capabilities & permissions
├── lang/
│   └── en/
│       └── report_quizanalytics.php # Language strings
├── templates/
│   └── main_page.mustache          # Mustache UI template & export modal
├── export.php                      # Custom field Excel / CSV export engine
├── index.php                       # Main course report controller
├── lib.php                         # Course navigation hook
├── version.php                     # Plugin version metadata
├── LICENSE                         # GPL-3.0 License
└── README.md                       # Documentation
```

---

## 📄 License

This program is free software: you can redistribute it and/or modify it under the terms of the **GNU General Public License (GPL) version 3 or later**.
