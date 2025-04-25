<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Canceled | TickEtopia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Redirect after 5 seconds
        setTimeout(function() {
            window.location.href = "http://localhost:3000";
        }, 10000);
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-bg: #242424;
            --card-bg: #2a2a2a;
            --error: #e53e3e;
            --error-light: rgba(229, 62, 62, 0.1);
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

        .cancel-icon {
            background-color: var(--error-light);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .cancel-icon i {
            color: var(--error);
            font-size: 40px;
        }

        h1 {
            font-size: 2rem;
            color: var(--error);
            margin-bottom: 16px;
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .suggestions {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }

        .suggestions h3 {
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .suggestions ul {
            padding-left: 20px;
        }

        .suggestions li {
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
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

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
        }

        .btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-outline:hover {
            background-color: rgba(49, 130, 206, 0.1);
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
            
            .btn-group {
                flex-direction: column;
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
            <div class="cancel-icon">
                <i class="fas fa-times"></i>
            </div>
            <h1>Checkout Canceled</h1>
            <p>Your payment process has been canceled. No charges have been made to your account.</p>
            
            <div class="suggestions">
                <h3>Were you experiencing issues?</h3>
                <ul>
                    <li>Check your payment details and try again</li>
                    <li>Ensure you have sufficient funds</li>
                    <li>Try using a different payment method</li>
                    <li>Contact our support team if you need assistance</li>
                </ul>
            </div>
            
            <div class="btn-group">
                <a href="http://localhost:3000" class="btn">Back to TickEtopia</a>
            </div>
            <p class="redirect-info">You will be redirected to the shop in 10 seconds...</p>
        </div>
        <div class="footer">
            <p>Need help? Contact our support team</p>
        </div>
    </div>
</body>

</html>