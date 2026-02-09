<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - MiniBee Honey</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fcd34d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .thank-you-container {
            max-width: 600px;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .thank-you-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .thank-you-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #FFC107, #FF9800, #FFC107);
        }

        .success-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out 0.2s both;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon svg {
            width: 60px;
            height: 60px;
            color: white;
        }

        .thank-you-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #F0403A;
            margin-bottom: 15px;
            animation: fadeIn 0.6s ease-out 0.3s both;
        }

        .thank-you-subtitle {
            font-size: 1.5rem;
            font-weight: 600;
            color: #0FA298;
            margin-bottom: 20px;
            animation: fadeIn 0.6s ease-out 0.4s both;
        }

        .thank-you-message {
            font-size: 1.1rem;
            color: #26244D;
            line-height: 1.8;
            margin-bottom: 30px;
            padding: 0 10px;
            animation: fadeIn 0.6s ease-out 0.5s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .invoice-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            border: 2px dashed #dee2e6;
            animation: fadeIn 0.6s ease-out 0.6s both;
        }

        .invoice-info p {
            margin: 0;
            font-size: 1rem;
            color: #495057;
        }

        .invoice-info a {
            color: #0FA298;
            font-weight: 700;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.2s;
        }

        .invoice-info a:hover {
            color: #0d8a82;
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
            animation: fadeIn 0.6s ease-out 0.7s both;
        }

        .btn-custom {
            padding: 14px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-family: 'Noto Sans Bengali', sans-serif;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #0FA298, #0d8a82);
            color: white;
            box-shadow: 0 4px 15px rgba(15, 162, 152, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 162, 152, 0.4);
            color: white;
        }

        @media (min-width: 768px) {
            .action-buttons {
                flex-direction: row;
                justify-content: center;
            }

            .thank-you-card {
                padding: 60px 50px;
            }

            .thank-you-title {
                font-size: 3rem;
            }

            .thank-you-subtitle {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 576px) {
            .thank-you-card {
                padding: 40px 25px;
            }

            .thank-you-title {
                font-size: 2rem;
            }

            .thank-you-subtitle {
                font-size: 1.25rem;
            }

            .thank-you-message {
                font-size: 1rem;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="thank-you-container">
        <div class="thank-you-card">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                </svg>
            </div>

            <h1 class="thank-you-title">ধন্যবাদ !</h1>
            <h2 class="thank-you-subtitle">আপনার অর্ডারটি গ্রহন করা হয়েছে</h2>
            <p class="thank-you-message">
                আমাদের একজন বিক্রয় প্রতিনিধি অর্ডারটি নিশ্চিত করার জন্য শীঘ্রই আপনাকে কল করবে!
            </p>

            <div class="invoice-info">
                <p>
                    আপনার অর্ডারের জন্য ইনভয়েস নম্বর: 
                    <b>
                        <a title="Print this invoice" target="_blank" href="{{ route('front.orders.show', [$order->id]) }}">
                            #{{ $order->invoice_no }}
                        </a>
                    </b>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
