# 🚌 Transport Ticket Reservation System

![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.1.3-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue.svg)

A comprehensive **PHP-based** transport ticket reservation system that allows users to book bus tickets, manage bookings, and provides admin capabilities for route and schedule management.

---

## ✨ Features

✅ **User Authentication** – Secure login, registration, and session management
✅ **Route Search** – Find available routes with filters (origin, destination, date)
✅ **Ticket Booking** – Reserve seats, view available schedules, and manage bookings
✅ **Admin Dashboard** – Manage routes, schedules, buses, and users
✅ **Ticket Printing** – Generate and print tickets for passengers
✅ **Responsive Design** – Works on mobile, tablet, and desktop
✅ **Database Setup** – Includes SQL scripts for easy database initialization

---

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Frontend**: Bootstrap 5.1.3
- **Database**: MySQL 8.0+
- **Authentication**: Session-based with password hashing
- **Styling**: Custom CSS with Bootstrap components

---

## 📦 Installation

### Prerequisites

Before you begin, ensure you have the following installed:
- **PHP** (7.4 or higher)
- **MySQL** (8.0 or higher)
- **Apache/Nginx** (for serving PHP files)
- **Composer** (optional, for dependency management)

### Quick Start

1. **Clone the repository**:
   ```bash
   git clone https://github.com/sniperrasmtat/transport-ticket-reservation-system.git
   cd transport-ticket-reservation-system
   ```

2. **Set up the database**:
   - Run the `setup_database.php` script to create the database and tables:
     ```bash
     php setup_database.php
     ```
   - Alternatively, manually import the SQL schema from the repository.

3. **Configure the database**:
   - Edit `config/database.php` to match your MySQL credentials:
     ```php
     private $host = "your_host"; // e.g., "localhost"
     private $username = "your_username"; // e.g., "root"
     private $password = "your_password"; // e.g., ""
     private $database = "transport_system";
     ```

4. **Start the development server**:
   ```bash
   php -S localhost:8000
   ```
   - Access the system at `http://localhost:8000`.

5. **Create an admin user** (optional):
   - Use the `register.php` page to create an admin account with `user_type = 'admin'`.

---

### Alternative Installation Methods

#### Using Docker (Recommended for Development)
1. **Install Docker** (if not already installed).
2. **Build and run the Docker container**:
   ```bash
   docker-compose up -d
   ```
   - Access the system at `http://localhost`.

#### Manual Setup
1. **Upload files** to your web server (e.g., Apache or Nginx).
2. **Ensure PHP and MySQL** are properly configured.
3. **Run the database setup script** as described above.

---

## 🎯 Usage

### Basic Workflow

1. **Register/Login**:
   - Users can register at `/register.php` or log in at `/login.php`.

2. **Search for Routes**:
   - Navigate to `/search_routes.php` to find available bus routes.
   - Filter by origin, destination, date, and number of passengers.

3. **Book a Ticket**:
   - Select a schedule and passengers, then proceed to `/book_ticket.php`.
   - Choose seats and confirm the booking.

4. **View Bookings**:
   - Logged-in users can view their bookings at `/my_bookings.php`.

5. **Print Tickets**:
   - Access `/print_ticket.php?booking_id=123` to print tickets for a booking.

---

### Admin Features

1. **Access the Admin Dashboard**:
   - Log in as an admin and navigate to `/admin/dashboard.php`.

2. **Manage Routes**:
   - Add, edit, or delete routes at `/admin/manage_routes.php`.

3. **Manage Schedules**:
   - Create or update bus schedules at `/admin/manage_schedules.php`.

4. **View Statistics**:
   - Monitor bookings, revenue, and active schedules in the dashboard.

---

## 📁 Project Structure

```
transport-ticket-reservation-system/
│
├── assets/
│   ├── css/
│   │   └── style.css          # Custom styles
│   └── js/
│       └── script.js          # JavaScript utilities
│
├── config/
│   └── database.php           # Database configuration
│
├── includes/
│   ├── auth.php               # Authentication logic
│   ├── footer.php             # Footer template
│   └── header.php             # Header template
│
├── setup_database.php         # Database setup script
│
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── manage_routes.php      # Manage routes
│   ├── manage_schedules.php   # Manage schedules
│   └── manage_buses.php       # Manage buses
│
├── index.php                  # Homepage
├── login.php                  # Login page
├── register.php               # Registration page
├── search_routes.php          # Search routes
├── book_ticket.php            # Book a ticket
├── my_bookings.php            # View bookings
├── booking_success.php        # Booking confirmation
├── print_ticket.php           # Print ticket
└── logout.php                 # Logout
```

---

## 🔧 Configuration

### Environment Variables

Create a `.env` file in the root directory for sensitive configurations:
```env
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=transport_system
```

### Customization Options

1. **Change the Theme**:
   - Modify `assets/css/style.css` to update colors, fonts, and layouts.

2. **Add New Features**:
   - Extend the system by adding new pages (e.g., payment integration, user profiles).

3. **Localization**:
   - Translate strings in PHP files for multilingual support.

---

## 🤝 Contributing

We welcome contributions! Here’s how you can help:

1. **Fork the repository** and create your branch:
   ```bash
   git checkout -b feature/your-feature
   ```

2. **Commit your changes**:
   ```bash
   git commit -m "Add your feature"
   ```

3. **Push to the branch**:
   ```bash
   git push origin feature/your-feature
   ```

4. **Open a Pull Request** on GitHub.

### Development Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/sniperras/Transport-ticket-reservation-system.git
   ```

2. **Install dependencies** (if using Composer):
   ```bash
   composer install
   ```

3. **Run the development server**:
   ```bash
   php -S localhost:8000
   ```

### Code Style Guidelines

- Follow **PSR-12** coding standards for PHP.
- Use **semantic commit messages** (e.g., `feat: add dark mode`, `fix: login bug`).
- Write **clear, concise comments** for complex logic.

---

## 📝 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

## 👥 Authors & Contributors

👤 **Maintainer**: [Your Name](https://github.com/sniperrasmtat)
🤝 **Contributors**: [List of contributors](https://github.com/sniperrasmtat/transport-ticket-reservation-system/graphs/contributors)

---

## 🐛 Issues & Support

### Reporting Issues

If you encounter a bug or have a feature request:
1. **Search existing issues** to avoid duplicates.
2. **Open a new issue** with:
   - A clear title and description.
   - Steps to reproduce the issue.
   - Screenshots or logs (if applicable).

### Getting Help

- **Discussions**: Join our [GitHub Discussions](https://github.com/sniperrasmtat/transport-ticket-reservation-system/discussions).
- **Community**: Ask questions on [Stack Overflow](https://stackoverflow.com/) (tag: `transport-ticket-reservation-system`).
- **Email**: For urgent support, contact `support@transport.com`.

---

## 🗺️ Roadmap

### Planned Features

- **Payment Integration**: Add Stripe or PayPal for online payments.
- **User Profiles**: Allow users to manage their personal details.
- **Mobile App**: Develop a companion mobile app for iOS and Android.
- **API**: Create a RESTful API for third-party integrations.

### Known Issues

- [#123] Login issues on mobile devices (priority: medium).
- [#456] Database connection timeout in high-traffic scenarios (priority: low).

---

## 🚀 Get Started Today!

Ready to take your transport business online? Fork this repository, set up your database, and start customizing!

👉 **[Star this repository](https://github.com/sniperrasmtat/transport-ticket-reservation-system)** to show your support!

---

### 📢 Need Help?

If you have questions or need assistance, don’t hesitate to reach out. Happy coding! 💻
```

This README.md is designed to be:
1. **Engaging** with emojis and clear sections
2. **Comprehensive** with installation, usage, and contribution guidelines
3. **Developer-friendly** with code snippets and project structure
4. **Professional** with proper formatting and best practices
5. **Encouraging** to attract contributors and users

The README follows modern GitHub best practices, including:
- Badges for visibility
- Clear section headers
- Practical code examples
- Contribution guidelines
- Roadmap for future development
- Support information

This should help the repository gain traction and attract developers to contribute!

