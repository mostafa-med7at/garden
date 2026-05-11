from docx import Document
from docx.shared import Pt, RGBColor, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

def add_code(doc, text):
    p = doc.add_paragraph()
    p.style = doc.styles['Normal']
    run = p.add_run(text)
    run.font.name = 'Courier New'
    run.font.size = Pt(9)
    run.font.color.rgb = RGBColor(0x1a, 0x53, 0x76)
    p.paragraph_format.left_indent = Inches(0.4)

def add_bullet(doc, text, level=0):
    p = doc.add_paragraph(text, style='List Bullet')
    p.paragraph_format.left_indent = Inches(0.4 + level * 0.25)

def section(doc, title, level=1):
    doc.add_heading(title, level=level)

doc = Document()

# ── TITLE PAGE ──────────────────────────────────────────────────────────────
t = doc.add_heading('Community Garden Management System', 0)
t.alignment = WD_ALIGN_PARAGRAPH.CENTER
p = doc.add_paragraph('Full Technical Documentation — Part 1 of 4')
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p = doc.add_paragraph('Project Overview · Folder Structure · Config · Core · Auth · Includes')
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
doc.add_paragraph('')

# ── 1. PROJECT OVERVIEW ──────────────────────────────────────────────────────
section(doc, '1. Project Overview')
doc.add_paragraph(
    'The Community Garden Management System is a full-stack PHP web application built entirely '
    'from scratch — no external frameworks like Laravel or Symfony are used. It was originally '
    'written in a procedural (step-by-step) PHP style and was then fully refactored into the '
    'Model-View-Controller (MVC) pattern with Object-Oriented Programming (OOP) principles. '
    'This document explains every folder, every file, every class, every function, and every '
    'design decision so that even a teammate with zero prior experience can understand the '
    'entire codebase.'
)
doc.add_paragraph(
    'The system allows community garden members, wardens, and administrators to manage:'
)
for f in [
    'Land — garden plot grid, lease agreements, soil health tracking, compost logging, pest reports, waitlist.',
    'Resources — tool library (check-out/check-in), consumable inventory, seed bank.',
    'Volunteer — community tasks & hours, shift scheduling & swapping, voting/polls, emergency broadcast, security incidents.',
    'Marketplace — flash produce trades, produce donations with karma points, advice Q&A board.',
    'Notifications — in-app & email notifications, pest alerts, waitlist notifications.',
    'Reports & Admin — CSV export of all modules, audit trail, user management (RBAC).',
    'Media — secure file upload and management.',
]:
    add_bullet(doc, f)

# ── 2. TECHNOLOGY STACK ──────────────────────────────────────────────────────
section(doc, '2. Technology Stack')
doc.add_paragraph(
    'Understanding which technology does what is the first step. Here is what we used:'
)
for t2 in [
    'PHP 8+ (Backend) — PHP is a server-side scripting language. It runs on the server and builds the HTML pages that the browser receives. We used it for all business logic, database access, and routing.',
    'MySQL via XAMPP (Database) — MySQL is the database where all data is stored permanently (users, plots, leases, tools, etc.). XAMPP is a local development server package that bundles Apache (the web server) and MySQL together.',
    'HTML5 & CSS3 (Frontend Structure & Styling) — HTML defines the page structure. CSS controls colors, layout, and fonts. We use Bootstrap 5 (a CSS library) loaded from a CDN for responsive layout components.',
    'JavaScript (Frontend Behaviour) — Used for interactive elements: confirmation dialogs, toggling sections, and AJAX sensor pings on the plot map.',
    'PDO (PHP Data Objects) — A built-in PHP library we use to talk to MySQL. It is safer than the older mysql_ functions because it supports prepared statements which prevent SQL injection attacks.',
    'python-docx (Documentation) — A Python library used to generate these Word documents automatically from code.',
]:
    add_bullet(doc, t2)

# ── 3. WHY MVC? ───────────────────────────────────────────────────────────────
section(doc, '3. Why MVC? What Does It Mean?')
doc.add_paragraph(
    'MVC stands for Model-View-Controller. It is an architectural pattern — a way of organising '
    'your code so that different responsibilities are separated from each other. Think of it like '
    'a restaurant:'
)
for analogy in [
    'The MODEL is the kitchen — it knows how to get and store data. It talks to the database.',
    'The VIEW is the plate of food served to the customer — it is what the user sees (HTML).',
    'The CONTROLLER is the waiter — it takes the customer\'s order (HTTP request), passes it to the kitchen (Model), and brings back the result on a plate (View).',
]:
    add_bullet(doc, analogy)
doc.add_paragraph(
    'Before MVC, this project had all three responsibilities mixed together in single files. '
    'Database queries, business logic, and HTML were all in one place, making the code hard to '
    'read, test, and maintain. After the MVC refactor, each layer has one clear job.'
)

# ── 4. COMPLETE FOLDER STRUCTURE ─────────────────────────────────────────────
section(doc, '4. Complete Folder Structure')
doc.add_paragraph(
    'Below is every folder and its role. All paths are relative to C:\\xampp\\htdocs\\garden\\'
)
structure = [
    ('garden/ (project root)', 'The root of the entire application.'),
    ('  index.php', 'The main entry point. Loads config, checks login, and hands off to DashboardController.'),
    ('  database.sql', 'The SQL dump file used to create and populate all database tables.'),
    ('  generate_doc.py', 'A Python script that auto-generates documentation Word files.'),
    ('  config/', 'Application-wide settings and helper functions.'),
    ('    db.php', 'Database connection using the Singleton pattern.'),
    ('    constants.php', 'All business constants (rates, weights) and global helper functions.'),
    ('    schema.sql', 'The current database schema (table definitions).'),
    ('    migrate_grid.sql', 'A migration script that added grid_x, grid_y columns to plots table.'),
    ('  core/', 'The base classes that all Models and Controllers inherit from.'),
    ('    Controller.php', 'Abstract base class. Provides model() and view() helper methods.'),
    ('    Model.php', 'Abstract base class. Gives all models a $db database connection.'),
    ('    View.php', 'Static helper for rendering view files.'),
    ('  auth/', 'Login, registration, and logout — public-facing pages.'),
    ('    login.php', 'Handles sign-in form and session creation.'),
    ('    register.php', 'Handles new account creation and gate code generation.'),
    ('    logout.php', 'Destroys the session and redirects to login.'),
    ('  includes/', 'Shared HTML fragments included on every page.'),
    ('    header.php', 'The navigation bar, page title, Bootstrap CSS links, flash message display.'),
    ('    footer.php', 'Closing HTML tags, Bootstrap JS, and the custom app.js script link.'),
    ('  admin/', 'Admin-only pages that do not follow full MVC (older style, still procedural).'),
    ('    users.php', 'Full user management: add, edit, deactivate, delete, search users.'),
    ('  controllers/', '22 controller files. One per feature module. Receives requests, runs logic, calls models, renders views.'),
    ('  models/', '22 model files. One per feature module. All database queries live here.'),
    ('  views/', '26 view files. Pure HTML + minimal PHP. No database queries allowed here.'),
    ('  modules/', 'Thin router files. These are the URLs the browser visits. They load config and hand off to a Controller.'),
    ('    land/', 'Routes for plots, leases, soil, compost, waitlist, pest reports, audit.'),
    ('    resources/', 'Routes for tools, seeds, consumables, penalties.'),
    ('    volunteer/', 'Routes for tasks, shifts, voting, incidents, emergency broadcast.'),
    ('    marketplace/', 'Routes for flash trades and advice board.'),
    ('    media/', 'Route for file manager.'),
    ('    notifications/', 'Routes for admin email centre and personal notifications.'),
    ('    reports/', 'Routes for reports dashboard and CSV export.'),
    ('  assets/', 'Static files served directly to the browser.'),
    ('    css/style.css', 'The entire custom stylesheet — colors, layout, cards, nav bar.'),
    ('    js/app.js', 'Custom JavaScript for confirmations, toggles, and AJAX sensor pings.'),
    ('    uploads/', 'User-uploaded files (tool images, inspection photos, media files).'),
    ('  documents/', 'This folder — auto-generated documentation Word files.'),
]
for path, desc in structure:
    p = doc.add_paragraph()
    run1 = p.add_run(path.ljust(40))
    run1.font.name = 'Courier New'
    run1.font.size = Pt(9)
    run1.font.bold = True
    run2 = p.add_run(desc)
    run2.font.size = Pt(10)

# ── 5. CONFIG FOLDER ─────────────────────────────────────────────────────────
section(doc, '5. The config/ Folder')
doc.add_paragraph(
    'The config/ folder contains application-wide settings that every other file needs. '
    'It is loaded first, before anything else runs.'
)

section(doc, '5.1 config/db.php — Database Connection', level=2)
doc.add_paragraph(
    'This file establishes the connection to the MySQL database and makes it available '
    'to the entire application. It uses two important patterns:'
)
section(doc, 'The Singleton Design Pattern', level=3)
doc.add_paragraph(
    'A Singleton ensures that only ONE instance of an object ever exists. Here, only ONE '
    'database connection is ever open at a time, no matter how many parts of the application '
    'need it. This saves memory and avoids conflicts. The class is called Database.'
)
doc.add_paragraph('The class has these parts:')
for item in [
    'private static $instance = null  —  A class-level variable that holds the one-and-only connection object. It starts as null (nothing).',
    'private $connection  —  The actual PDO connection object stored inside the instance.',
    'private function __construct()  —  The constructor is private, meaning no external code can call new Database(). It builds the DSN (Data Source Name) string and creates the PDO connection. It uses a try/catch block — if the connection fails, it stops the application and shows an error.',
    'public static function getInstance()  —  This is the only way to get the database. It checks if $instance is null; if yes, it creates one new Database(), stores it, and returns it. If it already exists, it just returns the existing one. This guarantees exactly one connection.',
    'public function getConnection()  —  Returns the raw $connection (PDO object) so other classes can run queries.',
]:
    add_bullet(doc, item)
doc.add_paragraph('Built-in PHP features used:')
for item in [
    'new PDO($dsn, $user, $pass, $options)  —  Creates a PDO database connection. PDO is a built-in PHP class (PHP Data Objects).',
    'PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION  —  Tells PDO to throw exceptions when an SQL error occurs, instead of silently failing.',
    'PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC  —  Makes all query results return as associative arrays (e.g. $row["name"]) instead of numeric arrays.',
    'PDO::ATTR_EMULATE_PREPARES => false  —  Forces real prepared statements for security.',
    'die()  —  A built-in PHP function that stops the script and outputs a message.',
    'json_encode()  —  Converts a PHP array to a JSON string.',
]:
    add_bullet(doc, item)
section(doc, 'The getDB() helper function', level=3)
doc.add_paragraph(
    'A standalone function called getDB() is defined at the bottom of db.php. It simply calls '
    'Database::getInstance()->getConnection() and returns the PDO object. This is a backward-compatibility '
    'wrapper — older parts of the code (like admin/users.php) can call $db = getDB() without needing '
    'to know about the Singleton class.'
)

section(doc, '5.2 config/constants.php — Constants & Helper Functions', level=2)
doc.add_paragraph(
    'This is one of the most important files. It defines all the business rules as constants '
    'and provides global helper functions used throughout the application. '
    'Loading this file once makes all constants and functions available everywhere.'
)
section(doc, 'Constants Defined', level=3)
for item in [
    'APP_NAME = "Community Garden Manager"  —  The display name of the application, shown in the browser tab and email headers.',
    'APP_ROOT = dirname(__DIR__)  —  The absolute path to the project root folder. dirname() goes one folder up from config/. Used for building file paths.',
    'APP_URL = "http://localhost/garden"  —  The base URL. Used in all redirect() calls and link generation.',
    'UPLOAD_DIR  —  The absolute path to assets/uploads/ where user files are stored.',
    'BILLING_RATE_PER_SQM = 8.00  —  Base rental fee in pounds per square metre per year. Used in calculateRentalFee().',
    'SOIL_MULTIPLIER  —  An array: premium soil costs 30% more (x1.30), standard is unchanged (x1.00), poor soil costs 20% less (x0.80). Used in lease billing.',
    'MEMBERSHIP_DISCOUNT  —  An array: premium members get 10% off, senior members 15% off, standard members no discount. Used in lease billing.',
    'LATE_FINE_PER_DAY = 1.50  —  Fine charged per day a tool is returned late.',
    'LATE_SERVICE_HOURS_PER_DAY = 0.5  —  Community service hours assigned per day a tool is returned late (alternative to paying the fine).',
    'PRIORITY_POINTS_WEIGHT = 0.6  —  Waitlist score formula: 60% of the score comes from community points.',
    'PRIORITY_RESIDENCY_WEIGHT = 0.4  —  Waitlist score formula: 40% of the score comes from how many months the member has lived in the area.',
    'KARMA_PER_KG = 10  —  For every 1 kg of produce donated, the user earns 10 karma points.',
    'MONTHLY_SERVICE_HOURS = 4.0  —  Members are required to log 4 volunteer hours per month.',
    'ALLERGEN_CATEGORIES  —  An array listing allergen types: Nightshades, Tree Nuts, Legumes, Gluten, Brassicas, Alliums. Used to flag produce trades.',
    'SOIL_PH_MIN = 5.5 and SOIL_PH_MAX = 7.5  —  The safe range for soil pH. Outside this range, the soil is considered "at risk".',
]:
    add_bullet(doc, item)

section(doc, 'Helper Functions Defined in constants.php', level=3)
doc.add_paragraph(
    'These are global functions — not methods of a class — that can be called from anywhere '
    'after constants.php is loaded.'
)
for fn, desc in [
    ('startSession()', 'Checks if a PHP session is already running using session_status(). If not, it calls session_start(). This prevents the "session already started" error if a session exists.'),
    ('currentUser()', 'Calls startSession(), then reads $_SESSION["user"] (the logged-in user data). Returns null if no user is logged in. The ?array return type means it can return either an array or null.'),
    ('requireLogin()', 'Calls currentUser(). If it returns null (not logged in), it calls header("Location: ...") to redirect the browser to the login page and then exit() stops PHP execution. If the user IS logged in, it returns the user array so the calling script can use it.'),
    ('hasPermission($module, $action)', 'Checks the $_SESSION["permissions"] array (loaded at login) for an entry like "land:create". Returns true or false. Used by controllers to restrict actions.'),
    ('loadPermissions($roleId)', 'Queries the permissions table for all module:action pairs for a given role, and stores them in $_SESSION["permissions"]. Called once at login.'),
    ('auditLog($actionType, $module, $table, $targetId, $description)', 'Inserts a row into the audit_log table every time an important action happens (login, plot created, lease terminated, etc.). Records who did it, what they did, which record was affected, and from which IP address. This is Function 30 in the project requirements.'),
    ('setFlash($type, $msg)', 'Saves a one-time message in the session ($_SESSION["flash"]). For example, after creating a plot, it saves a success message. The next page load reads this message, displays it, and deletes it.'),
    ('getFlash()', 'Reads and deletes the flash message from the session. Called in header.php so the message appears once at the top of the next page.'),
    ('redirect($path)', 'Wraps header("Location: " . APP_URL . "/" . $path) and calls exit(). A convenience function to avoid repeating the APP_URL concatenation everywhere.'),
    ('e($s)', 'Short for "escape". Calls htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") to convert special HTML characters (< > " & ) into safe equivalents. MUST be used on every variable output in views to prevent XSS (Cross-Site Scripting) attacks.'),
    ('calculateRentalFee($areaSqm, $soilQuality, $membershipStatus)', 'Implements business Function 2. Formula: base = area × BILLING_RATE_PER_SQM, after_soil = base × SOIL_MULTIPLIER[soilQuality], final = after_soil × (1 - MEMBERSHIP_DISCOUNT[membership]). Returns an array with base_fee, multiplier, discount_pct, and total_fee (all rounded to 2 decimal places using round()).'),
    ('calculatePriorityScore($communityPoints, $residencyMonths)', 'Implements business Function 4. Formula: (communityPoints × 0.6) + (residencyMonths × 0.4). Returns a float. Higher score = higher priority on the waitlist.'),
    ('calculateLatePenalty($dueDate, $returnDate)', 'Implements business Function 16. Uses the built-in DateTime class to parse the two date strings. Calculates the difference using diff(). If the return date is after the due date, multiplies days by LATE_FINE_PER_DAY and LATE_SERVICE_HOURS_PER_DAY. Returns an array with days_late, fine_amount, service_hours.'),
    ('isSoilAtRisk($ph)', 'Implements part of Function 3. Returns true if the given pH value is below SOIL_PH_MIN or above SOIL_PH_MAX. Returns false if ph is null.'),
    ('isAllergen($category)', 'Implements part of Function 26. Uses in_array() to check if a produce category is in the ALLERGEN_CATEGORIES constant array.'),
    ('calculateKarmaPoints($quantity)', 'Implements part of Function 25. Uses preg_match() (a regular expression function) to extract the numeric part from a string like "2.5 kg". Multiplies by KARMA_PER_KG. Returns minimum 10 points.'),
]:
    p = doc.add_paragraph()
    r1 = p.add_run(fn + '  ')
    r1.bold = True
    r1.font.name = 'Courier New'
    r1.font.size = Pt(10)
    p.add_run('— ' + desc)
    p.paragraph_format.left_indent = Inches(0.4)

# ── 6. CORE FOLDER ───────────────────────────────────────────────────────────
section(doc, '6. The core/ Folder — Base Classes')
doc.add_paragraph(
    'The core/ folder contains three abstract base classes. These are the blueprint classes '
    'that all Controllers and Models inherit from. "Abstract" means you cannot create an '
    'object directly from these classes — they only exist to be extended by child classes.'
)

section(doc, '6.1 core/Controller.php — Base Controller', level=2)
doc.add_paragraph(
    'This is a tiny but vital class. It is declared abstract so it cannot be instantiated directly. '
    'It provides two protected methods that every controller in the system uses:'
)
for fn, desc in [
    ('model($model)',
     'Takes a string like "PlotModel". Uses require_once to load the file models/PlotModel.php. '
     'Then uses the PHP dynamic class instantiation syntax new $model() to create a new object '
     'of that class and returns it. This means a controller can load any model by name without '
     'knowing its file path.'),
    ('view($view, $data = [])',
     'Takes a view name like "land_plots" and a data array like ["plots" => $plots]. '
     'It calls extract($data) — a built-in PHP function that turns array keys into local '
     'variables (so $data["plots"] becomes $plots inside the view). Then it uses require_once '
     'to load and execute views/land_plots.php.'),
]:
    p = doc.add_paragraph()
    p.add_run(fn + '  ').bold = True
    p.add_run('— ' + desc)
    p.paragraph_format.left_indent = Inches(0.4)
doc.add_paragraph(
    'Built-in PHP used: require_once (loads a file once), extract() (converts array to variables), '
    '__DIR__ (current directory constant).'
)

section(doc, '6.2 core/Model.php — Base Model', level=2)
doc.add_paragraph(
    'Also abstract. Its constructor connects to the database and stores the connection in $this->db. '
    'Every model that extends Model automatically gets a $db property ready to use for SQL queries. '
    'It calls Database::getInstance()->getConnection() — the Singleton we defined in db.php.'
)

section(doc, '6.3 core/View.php — Static View Helper', level=2)
doc.add_paragraph(
    'Contains a single static method render($viewPath, $data). Works the same as the view() '
    'method in Controller. It is used in a few places where a full controller is not involved.'
)

# ── 7. AUTH FOLDER ───────────────────────────────────────────────────────────
section(doc, '7. The auth/ Folder — Login, Register, Logout')
doc.add_paragraph(
    'These three files handle everything related to user authentication. They are public-facing '
    '(no login required to view them, since you need to log in first).'
)

section(doc, '7.1 auth/login.php', level=2)
doc.add_paragraph('What it does step by step:')
for step in [
    '1. Calls startSession() and then currentUser(). If a user is already logged in, it redirects them straight to index.php — no need to log in again.',
    '2. If the browser is sending a POST request (the form was submitted), it reads email and password from $_POST.',
    '3. Calls getDB() to get the database connection.',
    '4. Runs a prepared statement: SELECT users + JOIN roles WHERE email = ? AND is_active = 1. The JOIN brings in the role name (admin, warden, etc.) from the roles table.',
    '5. Calls $stmt->execute([$email]) — this safely replaces the ? placeholder.',
    '6. Calls $stmt->fetch() to get the user row.',
    '7. If the user exists, calls password_verify($password, $user["password_hash"]) — a built-in PHP function that checks if the plain-text password matches the stored hash. This is the secure way; we never store passwords as plain text.',
    '8. If verified, stores a user array in $_SESSION["user"] with id, full_name, email, role_id, role_name, membership, karma, points, and credits.',
    '9. Calls loadPermissions($user["role_id"]) to load this user\'s permissions into the session.',
    '10. Calls auditLog("login", "auth", "users", $user["id"], "User logged in") to record the login event.',
    '11. Redirects to index.php.',
    '12. If login fails, sets $error which is displayed in the form.',
]:
    add_bullet(doc, step)
doc.add_paragraph('Demo accounts shown on the login page: admin@garden.com, warden@garden.com, alice@garden.com, bob@garden.com — all use password "password".')

section(doc, '7.2 auth/register.php', level=2)
doc.add_paragraph('What it does:')
for step in [
    '1. Validates that name, email, and password are provided. Uses filter_var($email, FILTER_VALIDATE_EMAIL) — a built-in PHP function — to check the email format.',
    '2. Checks strlen($password) >= 6 for minimum password length.',
    '3. Checks $password === $confirm to ensure both password fields match.',
    '4. Queries the database to check if the email is already registered (SELECT id FROM users WHERE email = ?).',
    '5. If all checks pass, generates a unique gate code using: "GATE" + strtoupper(substr(md5($email . time()), 0, 6)). md5() is a built-in PHP hashing function, substr() takes the first 6 characters, strtoupper() converts to uppercase.',
    '6. Hashes the password: password_hash($password, PASSWORD_DEFAULT) — the secure way to store passwords in PHP.',
    '7. Inserts the new user with role_id = 4 (standard member) and shows the gate code in a success message.',
]:
    add_bullet(doc, step)

section(doc, '7.3 auth/logout.php', level=2)
doc.add_paragraph(
    'Calls startSession() and currentUser(). If a user is logged in, calls auditLog() to '
    'record the logout. Then calls session_destroy() — a built-in PHP function that completely '
    'wipes the session. Finally, redirects to the login page.'
)

# ── 8. INCLUDES FOLDER ───────────────────────────────────────────────────────
section(doc, '8. The includes/ Folder — Shared Page Layout')
doc.add_paragraph(
    'These two files are included at the top and bottom of every page view. '
    'They contain the navigation bar (header) and the closing HTML (footer).'
)

section(doc, '8.1 includes/header.php', level=2)
doc.add_paragraph('What it does:')
for step in [
    'Calls getFlash() to retrieve any pending flash message (e.g. "Plot created successfully").',
    'Queries the database for unread notifications count: SELECT COUNT(*) FROM notifications_log WHERE user_id = ? AND is_read = 0. This powers the red badge on the bell icon in the navigation bar.',
    'Outputs the full HTML <head> section: Bootstrap 5 CSS, Bootstrap Icons, and the custom style.css.',
    'Renders the navigation bar. The nav items shown depend on the user\'s role: all logged-in users see Plots, Tools, Seeds, Volunteer, Market, Files. Admin and warden users additionally see the Admin dropdown with options for Manage Users, Emails & Alerts, Reports, and System Audit.',
    'Shows the user\'s role badge, full name, karma points pill, and sign-out button.',
    'Displays the flash message (if any) as a dismissible Bootstrap alert.',
    'Opens the <main class="main-content"> tag.',
]:
    add_bullet(doc, step)

section(doc, '8.2 includes/footer.php', level=2)
doc.add_paragraph(
    'Closes the </main> tag. Renders a simple footer with the app name and current year '
    '(date("Y") — a built-in PHP function). Includes Bootstrap JS bundle and the custom app.js. '
    'Closes </body> and </html>.'
)

doc.save('documents/Part1_Overview_Config_Core_Auth.docx')
print('Part 1 saved.')
