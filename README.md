# 🏠 Hostel Management System

A full-featured **multi-tenant property rental marketplace** built with PHP, MySQL, and Bootstrap. Property owners list and manage their hostels, clients browse and book rooms, and a super admin oversees the entire platform.

---

## ✨ Key Features

### Public / Guest
- **Browse Properties** — View all approved hostels with images, amenities, pricing, and room types
- **Hostel Details** — Photo gallery, amenity list, room availability, and reviews
- **Wishlist** — Save favourite hostels (requires login)
- **Client Registration** — Self-service sign-up with automatic login

### Client Dashboard
- Personalised welcome with booking stats (status, room, payments, available hostels)
- **Book a Room** — Select hostel → room type → check availability → confirm
- **Payments** — Submit payment with proof upload (M-Pesa, Bank Transfer, Cash)
- **View Invoice** — Downloadable / printable invoices
- **Reviews & Ratings** — Rate and review hostels via modal dialog
- **Profile Management** — Update details and profile picture
- **Messaging** — Send and receive messages with landlords / admins
- **Notifications** — In-app notification centre
- **Activity Log** — View personal login and action history
- **Password Reset** — Token-based forgot / reset password flow

### Landlord Dashboard
- **Property Management** — Add, edit, and view hostels with multi-image upload
- **Room Management** — Add / edit rooms grouped by hostel, with seater and fee details
- **Booking Management** — Create bookings, approve / reject booking requests
- **Client Management** — View and manage client accounts and profiles
- **Payments** — Track and verify tenant payments, configure payment settings
- **Messaging & Notifications** — Communicate with clients and admins
- **Activity Logs** — Monitor system activity

### Super Admin Dashboard
- **Platform Overview** — Aggregate stats across all tenants
- **Hostel Approvals** — Approve, reject, or suspend hostel listings
- **Tenant Management** — Add, suspend, or activate property-owner tenants
- **User Management** — Manage all users across the platform
- **Impersonation** — Log in as any tenant admin for support
- **All landlord features** plus cross-tenant visibility

### System-Wide
- **Multi-Tenancy** — Each landlord (tenant) sees only their own data
- **Sapphire Veil Theme** — Premium dark UI with glassmorphism, 70 % opacity blur, and high-contrast badges
- **Role-Based Access** — `super_admin`, `landlord`, `client`
- **Profile Pictures** — Upload and display across header navigation
- **Responsive Design** — Mobile-friendly layouts

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.4+ (procedural) |
| Database | MySQL / MariaDB 10.4+ |
| Frontend | Bootstrap 4, jQuery, Font Awesome |
| Styling | Custom CSS (Sapphire Veil theme) |
| Server | Apache (XAMPP recommended) |
| Image Processing | PHP GD Library |

---

## 🚀 Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- PHP **7.4** or higher
- GD library enabled in `php.ini`

### Installation

1. **Clone / copy** the project into your web root:
   ```
   C:\xampp\htdocs\HostelManagement-PHP\
   ```

2. **Create the database**:
   - Open phpMyAdmin → Create a database named `hostelmsphp`
   - Import `DATABASE FILE/unified_database.sql`

3. **Configure the database connection** in `includes/dbconn.php`:
   ```php
   $con = mysqli_connect("localhost", "root", "", "hostelmsphp");
   ```

4. **Start Apache and MySQL** from the XAMPP Control Panel.

5. **Open in browser**:
   ```
   http://localhost/HostelManagement-PHP/
   ```

### Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `super@hostel.com` | `Test@12345` |
| Landlord (Tenant Admin) | `admin@mail.com` | `Test@123` |
| Client | `ross@mail.com` | `123456` |

> [!NOTE]
> Passwords are stored as MD5 hashes (legacy). For production, migrate to `password_hash()`.

---

## 📂 Project Structure

```
HostelManagement-PHP/
├── admin/                   # Super Admin & Tenant Admin panel
│   ├── dashboard.php        #   Tenant admin dashboard
│   ├── super_dashboard.php  #   Super admin dashboard
│   ├── manage-hostels.php   #   Property listings CRUD
│   ├── manage-rooms.php     #   Room management (grouped by hostel)
│   ├── manage-approvals.php #   Hostel approval workflow
│   ├── manage-tenants.php   #   Tenant (landlord) management
│   ├── manage-users.php     #   User management
│   ├── manage-clients.php   #   Client account management
│   ├── bookings.php         #   Booking creation & management
│   ├── booking-requests.php #   Pending booking approvals
│   ├── payments.php         #   Payment verification
│   ├── messages.php         #   Messaging system
│   ├── activity-logs.php    #   System activity logs
│   └── ...
│
├── landlord/                # Landlord (Property Owner) panel
│   ├── dashboard.php        #   Landlord overview
│   ├── add-hostel.php       #   Add new property
│   ├── edit-hostel.php      #   Edit property details
│   ├── manage-hostels.php   #   View own properties
│   ├── manage-rooms.php     #   Room CRUD
│   ├── bookings.php         #   Booking management
│   ├── booking-requests.php #   Approve / reject bookings
│   ├── payments.php         #   Payment tracking
│   ├── payment-settings.php #   Configure payment methods
│   ├── messages.php         #   Messaging
│   └── ...
│
├── client/                  # Client (Tenant) panel
│   ├── dashboard.php        #   Client overview with stats
│   ├── book-hostel.php      #   Room booking flow
│   ├── make-payment.php     #   Submit payment with proof
│   ├── view-invoice.php     #   View / print invoice
│   ├── write-review.php     #   Submit hostel review
│   ├── room-details.php     #   View room information
│   ├── messages.php         #   Messaging
│   ├── profile.php          #   Profile management
│   └── ...
│
├── includes/                # Shared PHP components
│   ├── dbconn.php           #   MySQLi database connection
│   ├── pdoconfig.php        #   PDO database connection
│   ├── check-login.php      #   Authentication guard
│   ├── tenant_manager.php   #   Multi-tenancy helper
│   ├── hostel-helper.php    #   Property query utilities
│   ├── image-upload-handler.php  # Image processing (resize, crop)
│   ├── notification-helper.php   # Notification creation
│   ├── log-helper.php       #   Activity logging
│   ├── toast-helper.php     #   Toast notification UI
│   ├── client-navigation.php    # Client header / nav
│   ├── client-sidebar.php   #   Client sidebar menu
│   ├── footer.php           #   Page footer
│   └── ...
│
├── assets/                  # Static resources
│   ├── css/                 #   Stylesheets (incl. sapphire-veil.css)
│   ├── js/                  #   JavaScript files
│   ├── libs/                #   Third-party libraries
│   └── images/              #   Static images
│
├── uploads/                 # User-uploaded media
│   └── hostels/             #   Property images
│
├── DATABASE FILE/
│   └── unified_database.sql #   Complete database schema + seed data
│
├── index.php                # Public browse page
├── login.php                # Unified login for all roles
├── client-registration.php  # Client sign-up
├── hostel-details.php       # Public property detail page
├── forgot-password.php      # Password reset request
├── reset-password.php       # Password reset form
└── README.md
```

---

## 🗄 Database Schema

The system uses **20 tables** in the `hostelmsphp` database:

| Table | Purpose |
|-------|---------|
| `users` | Unified user accounts (admin, landlord, client) with roles & profile pics |
| `admin` | Legacy admin credentials |
| `tenants` | Property-owner organisations (multi-tenancy) |
| `hostels` | Property listings with approval status |
| `hostel_images` | Multi-image gallery per property |
| `hostel_types` | Room type definitions (Single, Bedsitter, 1BR, etc.) |
| `hostel_type_mapping` | Links hostels to available room types with pricing |
| `hostel_amenities` | Links hostels to amenities |
| `amenities` | Master amenity list (WiFi, Parking, etc.) |
| `rooms` | Individual rooms with seater count, fees, and availability status |
| `bookings` | Room bookings with status workflow |
| `payments` | Payment records with proof uploads |
| `reviews` | Client ratings and comments for hostels |
| `wishlist` | Client saved/favourite hostels |
| `messages` | In-app messaging between users |
| `notifications` | System and user notifications |
| `password_resets` | Token-based password reset tracking |
| `user_activity_logs` | Detailed action audit trail |
| `adminlog` | Admin login history |
| `userlog` | Client login history |

---

## 🔧 Troubleshooting

### Images Not Displaying
- Ensure `uploads/hostels/` directory exists and has write permissions
- Verify `hostel_images` table has records
- Confirm image paths in the database match actual files on disk

### Properties Not Showing on Browse Page
- Hostel must have `status = 'approved'` in the `hostels` table
- Hostel should have at least one image uploaded
- Hostel must have room types assigned via `hostel_type_mapping`

### Bookings Not Working
- Client must be logged in
- Check that `hostel_id` is passed to the booking page
- Verify rooms exist for the selected hostel and are not already booked
- Room `status` must be `'available'`

### Image Upload Fails
- Enable the GD extension in `php.ini` (`extension=gd`)
- Increase `upload_max_filesize` and `post_max_size` in `php.ini`
- Verify `uploads/hostels/` has write permissions
- Accepted formats: JPG, PNG, GIF (max 5 MB)

### Password Reset Not Working
- Verify the `password_resets` table exists
- Check that your PHP mail configuration is set up (or use a local SMTP tool)

---

## 📄 License

This project is provided as-is for educational and demonstration purposes.

**Original System**: [CodeAstro](https://codeastro.com)
**Enhanced & Modernised**: 2026
