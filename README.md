AirG6 Airline Booking System

-- Project Description ----------------------------------------------------------------------------

AirG6 is a PHP-based airline booking platform that allows users to:
- search for flights, 
- book one-way or return tickets
- select seats and services
- manage their trip using a unique confirmation code

The system also includes an admin dashboard for managing flights, bookings, and users.

-- Features ---------------------------------------------------------------------------------------

👤 User Side (Guest Access)
- Search for one-way or return flights
- View available flights with date, route, and price
- Step-by-step booking process:
  - Flight selection
  - Passenger details
  - Baggage selection
  - Seat selection
  - Booking confirmation
- Auto-calculates total price based on number of passengers
- Displays real-time booking summary
- Generates a unique confirmation code
- “My Trips” page to:
  - View trip details
  - Cancel trip using confirmation code (no login required)

🛠️ Admin Dashboard (Login Required)
- Login authentication for admin
- Manage Flights (CRUD)
- Manage Bookings (View and Delete)
- Manage Users (View and Edit)
- View admin profile (edit details)

-- Technologies Used ------------------------------------------------------------------------------

- Frontend: HTML, CSS, Bootstrap
- Backend: PHP
- Database: MySQL
- Server: XAMPP (Apache, MySQL)

-- Screenshots ------------------------------------------------------------------------------------

### Homepage
![Homepage](screenshots/airg6_homepage.png)

### Booking Process
![Search Results](screenshots/airg6_bookFlights.png)

### Admin Dashboard
![Admin Dashboard](screenshots/airg6_adminDashboard.png)
