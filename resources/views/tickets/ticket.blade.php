<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .ticket {
            width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 8px;
            text-align: center;
            background-color: #f8f8f8;
        }
        .ticket-header {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .ticket-details {
            margin-top: 20px;
            font-size: 18px;
            color: #555;
        }
        .ticket-footer {
            margin-top: 30px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header">
            {{ $event->name }} - Ticket
        </div>
        <div class="ticket-details">
            <p>Customer: {{ $user->name }}</p>
            <p>Date: {{ \Carbon\Carbon::parse($event->date)->format('l, F j, Y \a\t g:i A') }}</p> <!-- Format the date properly -->
            <p>Location: {{ $event->location }}</p>
            <p>Ticket Number: {{ $ticket->ticket_number }}</p> <!-- Show ticket number here -->
        </div>
        <div class="ticket-footer">
            <p>Thank you for purchasing your ticket. Enjoy the event!</p>
        </div>
    </div>
</body>
</html>
