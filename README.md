# BlogSpace — PHP & MySQL Blog Web Application

A clean, responsive, and functional blog web application built using native PHP, MySQL, HTML5, CSS3, and vanilla JavaScript. Built as an IT undergraduate coursework project.

---

## Features

- **User Authentication**: Secure user registration and login system with password hashing (`password_hash()` using BCRYPT) and PHP session management.
- **Full CRUD Operations**: Users can write, view, edit, and delete their blog posts.
- **Role & Ownership Authorization**: Users can only edit or delete their own posts. Unauthorized actions are blocked both on the UI and backend script levels.
- **SQL Injection Prevention**: All database interactions use prepared statements with `mysqli` parameter binding.
- **Markdown Editor**: Integrated EasyMDE Markdown editor on post creation and editing forms with live toolbar formatting.
- **Safe Markdown Rendering**: Posts are rendered using Marked.js and sanitized with DOMPurify to prevent XSS vulnerabilities.
- **Featured Image Uploads**: Optional image upload support for posts (JPEG, PNG, GIF, WEBP up to 5MB) with thumbnail previews in post cards.
- **Environment Configuration**: Database credentials are kept in a separate `.env` file and excluded from version control.
- **Editorial Responsive Design**: Mobile-friendly layout styled with Google Fonts (*Merriweather* and *Lato*).

---

## Technologies Used

- **Backend**: PHP (Native / Procedural + OOP mysqli)
- **Database**: MySQL (InnoDB, UTF-8 Unicode)
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Libraries & CDN Assets**:
  - [EasyMDE](https://github.com/Ionaru/easy-markdown-editor) — Markdown Editor
  - [Marked.js](https://marked.js.org/) — Markdown Parser
  - [DOMPurify](https://github.com/cure53/DOMPurify) — HTML Sanitizer
  - [Google Fonts](https://fonts.google.com/) — Merriweather & Lato
- **Server Environment**: Apache & MySQL (XAMPP / WAMP / LAMP)

---

## Project Structure

```
blog-app/
├── .env.example              # Template for database credentials
├── .gitignore                # Git ignore rules (.env, uploads, IDE files)
├── index.php                 # Home page listing all posts
├── README.md                 # Project documentation
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   └── js/
│       └── script.js         # Client-side validation scripts
├── config/
│   └── config.php            # Environment loader and global constants
├── db/
│   └── schema.sql            # Database creation and table schemas
├── includes/
│   ├── db.php                # Database connection helper
│   ├── header.php            # Shared header and navigation template
│   └── footer.php            # Shared footer template
├── pages/
│   ├── create.php            # Create new blog post
│   ├── delete.php            # Secure post deletion handler
│   ├── edit.php              # Edit existing blog post
│   ├── login.php             # User login page
│   ├── logout.php            # Session termination script
│   ├── register.php          # User registration page
│   └── view.php              # Single post view with rendered markdown
└── uploads/
    └── .gitkeep              # Directory for uploaded featured images
```

---

## Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP 8.x + MySQL server setup)
- Git

### Installation & Setup

1. **Clone the repository** into your web server root:
   ```bash
   cd c:/xampp/htdocs/
   git clone https://github.com/sanduni112/blog-app.git
   ```

2. **Database Setup**:
   - Start Apache and MySQL from the XAMPP Control Panel.
   - Open phpMyAdmin in your browser: `http://localhost/phpmyadmin/`.
   - Create a new database named `blog_db` or import the `db/schema.sql` file directly.

3. **Configure Environment Variables**:
   - Copy `.env.example` to `.env` in the project root:
     ```env
     DB_HOST=localhost
     DB_USER=root
     DB_PASS=
     DB_NAME=blog_db
     ```
   - Update `DB_USER` and `DB_PASS` if your MySQL installation uses custom credentials.

4. **Verify Uploads Directory**:
   - Ensure the `uploads/` folder exists in the project root and has write permissions.

5. **Run the Application**:
   - Open your browser and navigate to:
     ```
     http://localhost/blog-app/
     ```

---

## Database Schema

The application uses two relational tables:

### `user` Table
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | INT | Primary Key, Auto Increment | Unique user ID |
| `username` | VARCHAR(50) | UNIQUE, NOT NULL | User login handle |
| `email` | VARCHAR(100) | UNIQUE, NOT NULL | User email address |
| `password` | VARCHAR(255) | NOT NULL | Hashed password |
| `role` | VARCHAR(20) | NOT NULL, DEFAULT 'user' | User authorization role |

### `blogPost` Table
| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | INT | Primary Key, Auto Increment | Unique post ID |
| `user_id` | INT | Foreign Key (user.id) | Author reference (ON DELETE CASCADE) |
| `title` | VARCHAR(255) | NOT NULL | Post title |
| `content` | TEXT | NOT NULL | Post markdown content |
| `featured_image` | VARCHAR(255) | DEFAULT NULL | Filename of uploaded image |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Last modified timestamp |

---

## Security Practices Implemented

- **Prepared Statements**: Used for all SQL queries (`$conn->prepare()`, `bind_param()`) preventing SQL injection.
- **Password Security**: Passwords are never stored in plain text; PHP's native `password_hash()` (BCRYPT) and `password_verify()` are used.
- **XSS Sanitization**: User outputs in HTML are escaped with `htmlspecialchars()`, and Markdown HTML output is sanitized with DOMPurify.
- **File Upload Protection**: Uploaded files are strictly checked for extension and MIME type, size-limited (5MB), and stored with randomly generated filenames (`uniqid()`) to prevent execution attacks.
- **CSRF & Ownership Checks**: Delete and Edit operations verify whether `user_id` matches `$_SESSION['user_id']` at the database level.
- **Environment Isolation**: `.env` is listed in `.gitignore` to prevent sensitive credentials from leaking to public repositories.

---

## Author

- **W.S.Sewwandee** ([@sanduni112](https://github.com/sanduni112))
- Email: [sanduniwelege20@gmail.com](mailto:sanduniwelege20@gmail.com)
