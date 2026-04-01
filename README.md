# NeoTracker — Gamified Habit Tracking System

NeoTracker is a gamified habit tracking system built around a Telegram bot and a WebApp interface.  
Users complete daily tasks, unlock missions, and progress through a branching narrative.

---

## 🔥 Key Features

- Habit tracking system (steps, water, calories, etc.)
- Mission-based progression
- Branching storyline with choices
- Telegram Bot integration
- WebApp dashboard for tracking and interaction
- REST API backend

---

## 🏗️ Architecture

The system consists of three main parts:

- **Telegram Bot** — user interaction entry point
- **Backend API (PHP)** — business logic and data processing
- **WebApp (Angular)** — user interface

Flow:
Telegram Bot → PHP API → MySQL
WebApp (Angular) → PHP API → MySQL


---

## 🧩 Tech Stack

**Backend:**
- PHP
- MySQL
- REST API

**Frontend:**
- Angular

**Integration:**
- Telegram Bot API

---

## 🎮 How It Works

1. User interacts with the Telegram bot  
2. Completes daily habits  
3. Unlocks missions  
4. Makes choices that affect story progression  
5. Tracks progress via WebApp  

---

## ⚙️ Technical Challenges

Some of the key challenges in this project:

- Managing user state across Telegram and WebApp
- Designing a flexible mission system
- Synchronizing progress between multiple interfaces
- Structuring game logic inside backend services

---

## 🔧 Improvements & Refactoring

The project originally started as a monolithic structure and was later improved:

- Refactored business logic into separate layers
- Improved API structure and endpoints
- Optimized database queries
- Reduced code duplication
- Improved maintainability

---

## 🚀 Future Improvements

- Demo mode for easier onboarding
- Improved UI/UX
- Migration to modern backend framework (Laravel / Node.js)
- Better test coverage

---

## 📸 Screenshots

(Add screenshots of WebApp here)

---

## 🎥 Demo

(Optional: add video link)

---

## 📬 Contact

- Email: your_email
- Telegram: your_username
