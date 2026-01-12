<!doctype html>
<html>
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Hotels - Admin</title>
  @vite(['resources/js/admin.js'])
</head>
<body>
  <div id="app" class="container">
    <h1>Hotels</h1>
    <div id="hotels-list"></div>

    <hr/>
    <h2>Price Quote</h2>
    <form id="quoteForm">
      <input name="hotelId" placeholder="hotel id" />
      <input name="roomId" placeholder="room id" />
      <input name="checkIn" placeholder="YYYY-MM-DD" />
      <input name="checkOut" placeholder="YYYY-MM-DD" />
      <button type="submit">Get Price</button>
    </form>
    <pre id="quoteResult"></pre>
  </div>
</body>
</html>
