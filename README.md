# 🚖 GrabWeb - Campus Ride Booking System

**GrabWeb** is a simplified ride-hailing web application prototype designed specifically for university campuses. Built in collaboration with **UiTM** and **Grab**, it aims to provide a safe, affordable, and convenient transport solution for students and staff within the campus grounds.

![Project Status](https://img.shields.io/badge/Status-Prototype-green)
![Tech Stack](https://img.shields.io/badge/HTML5-CSS3-blue)
![License](https://img.shields.io/badge/License-MIT-yellow)

## 📖 Table of Contents
- [About the Project](#-about-the-project)
- [Key Features](#-key-features)
- [Screenshots](#-screenshots)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
- [Future Improvements](#-future-improvements)
- [Credits](#-credits)

---

## 🎯 About the Project
GrabWeb addresses the transportation challenges faced by students and staff inside large university campuses (specifically modeled for **UiTM Machang**). Unlike standard ride-hailing apps, GrabWeb focuses on:
* **Fixed Routes:** Gate to Library, Hostel to Faculty, etc.
* **Affordable Student Rates:** Subsidized or fixed pricing.
* **Safety:** Verified drivers (students or staff) within the campus ecosystem.

> **Note:** This repository contains the **Frontend Prototype** (HTML/CSS/JS). It includes simulated logic for login and navigation but is not currently connected to a live backend database.

---

## ✨ Key Features

### 👤 For Customers (Students/Staff)
* **Ride Search Widget:** Select Pickup, Drop-off, and Date.
* **Ride Cards:** View driver details, car model, ratings, and price.
* **User Dashboard:** Access profile, ride history, and logout functionality.
* **Help Center:** FAQ and support for lost items or safety issues.

### 🚗 For Drivers
* **Registration:** Dedicated sign-up flow for verified drivers.
* **Manage Bookings:** (Concept) Interface to accept/reject rides.

### 🛡️ For Admins
* **Admin Dashboard:** Overview of total revenue, active drivers, and recent bookings.
* **System Management:** Tools to manage users and view reports.

---

## 📸 Screenshots

*(You can replace these image links with your own screenshots later)*

| Landing Page | Login Page |
|:---:|:---:|
| ![Landing Page](./images/screenshot-landing.png) | ![Login Page](./images/screenshot-login.png) |
| *Home page with Search Widget* | *Role-based Login (Customer/Driver/Admin)* |

| Admin Dashboard | User Profile |
|:---:|:---:|
| ![Admin Dashboard](./images/screenshot-admin.png) | ![User Profile](./images/screenshot-user.png) |
| *System overview and stats* | *Logged-in view with dropdown menu* |

---

## 📂 Project Structure

```bash
GrabWeb/
├── index.html           # Public Landing Page (Logout view)
├── index2.html          # Customer Dashboard (Logged-in view)
├── login.html           # Authentication (Login/Sign Up)
├── admin_dashboard.html # Admin Control Panel
├── help.html            # Help Center & FAQ
├── style.css            # Global Stylesheet
├── README.md            # Documentation
└── images/              # Assets (Logos, Icons, etc.)
    ├── grab-logo.png
    └── uitm-logo.png