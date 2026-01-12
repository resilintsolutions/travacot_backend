<!doctype html>
<html>
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - KPIs</title>
  @vite(['resources/js/admin.js'])
</head>
<body>
  <div id="app" class="container">
    <h1>KPIs</h1>
    <div id="kpi-cards">
      <div>Total Bookings: <span id="totalBookings">--</span></div>
      <div>Total Revenue: <span id="totalRevenue">--</span></div>
      <div>Average Booking Value: <span id="avgBookingValue">--</span></div>
    </div>
    <div>
      <button id="refreshBtn">Refresh Now</button>
    </div>

    <hr/>
    <a href="{{ url('/admin/hotels') }}">Manage Hotels</a>
  </div>
</body>
</html>
