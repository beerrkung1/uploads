<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กำลังปรับปรุงเว็บไซต์</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Reset Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styles */
        body {
            height: 100vh;
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        /* Container */
        .container {
            text-align: center;
            padding: 40px 30px;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
        }

        /* Heading */
        .content h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        /* Paragraph */
        .content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        /* Loader */
        .loader {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #fff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1.5s linear infinite;
            margin: 0 auto;
        }

        /* Animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 600px) {
            .content h1 {
                font-size: 2rem;
            }

            .content p {
                font-size: 1rem;
            }

            .loader {
                width: 50px;
                height: 50px;
                border-width: 6px;
            }
        }

        /* Optional: Add some animation to the container */
        .container {
            animation: fadeIn 2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>เว็บไซต์ของเรากำลังปรับปรุง</h1>
            <p>ขออภัยในความไม่สะดวก กรุณากลับมาเยี่ยมชมใหม่ในเร็วๆ นี้!</p>
            <div class="loader"></div>
        </div>
    </div>
</body>
</html>
