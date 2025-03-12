<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed Successfully</title>
</head>
<body>
    <h1>Thank you for your purchase, {{ $user->name }}!</h1>
    <p>Your order has been successfully placed. Here are the details:</p>
    <ul>
        <li>Order ID: {{ $orderDetails['order_id'] }}</li>
        <li>Total: ${{ $orderDetails['total'] }}</li>
    </ul>
</body>
</html>
