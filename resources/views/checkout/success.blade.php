<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Success | TickEtopia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        setTimeout(function () {
            window.location.href = "http://localhost:3000";
        }, 10000);

        async function generateTicket() {
            try {
                const userId = localStorage.getItem('user_id'); 
                const eventId = localStorage.getItem('event_id'); 

                console.log("User ID from localStorage: ", userId);
                console.log("Event ID from localStorage: ", eventId);

                if (!userId || !eventId) {
                    console.error('Missing userId or eventId');
                    return;
                }

                const response = await fetch('http://127.0.0.1:8000/generate-ticket', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        event_id: eventId
                    })
                });

                const data = await response.json();

                if (response.ok && data.pdf_url) {
                    console.log('Ticket generated:', data);
                    window.location.href = data.pdf_url;
                } else {
                    console.error('Error response from server:', data);
                }
            } catch (error) {
                console.error('Error generating ticket:', error);
            }
        }

        generateTicket();
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-bg: #242424;
            --card-bg: #2a2a2a;
            --success: #4bbf67;
            --success-light: rgba(75, 191, 103, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --accent: #3182ce;
            --accent-hover: #2b6cb0;
            --border: #3a3a3a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--primary-bg);
            color: var(--text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: var(--card-bg);
            width: 100%;
            max-width: 450px;
            border-radius: 16px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
        }

        .header {
            padding: 24px;
            position: relative;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-secondary);
            display: inline-block;
        }

        .logo span {
            color: var(--accent);
        }

        .content {
            padding: 32px 24px;
            text-align: center;
        }

        .success-icon {
            background-color: var(--success-light);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .success-icon i {
            color: var(--success);
            font-size: 40px;
        }

        h1 {
            font-size: 2rem;
            color: var(--success);
            margin-bottom: 16px;
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .info {
            background-color: rgba(49, 130, 206, 0.1);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }

        .info p {
            margin: 8px 0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .info i {
            margin-right: 10px;
            color: var(--accent);
        }

        .btn {
            background-color: var(--accent);
            color: white;
            padding: 12px 24px;
            font-size: 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .redirect-info {
            margin-top: 16px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .footer {
            padding: 16px 24px;
            background-color: rgba(0, 0, 0, 0.1);
            text-align: center;
            border-top: 1px solid var(--border);
        }

        .footer p {
            font-size: 0.85rem;
            margin: 0;
        }

        @media (max-width: 500px) {
            .container {
                border-radius: 12px;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            p {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">Tick<span>Etopia</span></div>
        </div>
        <div class="content">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p>Your order has been placed successfully. You will receive a confirmation email with all the details shortly.</p>
            
            <div class="info">
                <p><i class="fas fa-envelope"></i> Confirmation sent to your email</p>
                <p><i class="fas fa-ticket-alt"></i> Tickets will be available in your account</p>
                <p><i class="fas fa-clock"></i> Processing time: 1-2 minutes</p>
            </div>
            
            <a href="http://localhost:3000" class="btn">Back to TickEtopia</a>
            <p class="redirect-info">You will be redirected in 10 seconds...</p>
        </div>
        <div class="footer">
            <p>Thank you for choosing TickEtopia!</p>
        </div>
    </div>
</body>

</html>
