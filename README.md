# JejakPeribahasa - Malay Proverb Learning System 📖

A full-stack web application built with PHP and MySQL designed to centralize and modernize the learning of Malay proverbs (Peribahasa). This project was developed as part of a formal Software Engineering (CSC577) curriculum, complete with use case modeling and system flow documentation.

## 🎯 Project Objective & Problem Statement
Currently, learning Malay proverbs is fragmented across static textbooks and unengaging websites, making it time-consuming for students and teachers. **JejakPeribahasa** solves this by providing a centralized, interactive platform that aggregates over 100 proverbs complete with meanings, categories (Bidalan, Pepatah, Kiasan), and interactive community features.

## ⚙️ Tech Stack
* **Backend:** PHP
* **Database:** MySQL (phpMyAdmin)
* **Frontend:** HTML, CSS
* **Server:** XAMPP (Apache)

## 💡 Key System Features
* **Role-Based Access Control:** Distinct dashboards and permissions for Admins, Clerks (System Managers), and Users.
* **Peribahasa Reaksi (Community Forum):** An interactive space where users can communicate, ask questions, and reply using suitable proverbs.
* **Interactive Quizzes:** Built-in quiz module with automated scoring for users to test their knowledge.
* **Proverb Database:** Categorized viewing of proverbs with detailed meanings and contextual examples.
* **Favorites Tracking:** Users can save specific proverbs to their personal profiles.

---

## 🚀 How to Run Locally

1. **Prerequisites:** Ensure you have [XAMPP](https://www.apachefriends.org/index.html) installed.
2. **Database Setup:** 
   * Open XAMPP and start Apache and MySQL.
   * Go to `http://localhost/phpmyadmin`.
   * Create a new database named `peribahasa` and import the provided `peribahasa.sql` file.
3. **Application Setup:**
   * Place the `JejakPeribahasa` folder into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\JejakPeribahasa`).
4. **Launch:** Open your web browser and navigate to `http://localhost/JejakPeribahasa/index.php`.

### Test Credentials
* **Admin:** ID: `1` | Password: `1`
* **Clerk:** ID: `211` | Password: `123`
* **User:** Email: `admin@test.com` | Password: `123`
