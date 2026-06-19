<!DOCTYPE html>
<html>
<head>
    <title>Meal Coupon</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 20px;
            justify-items: center;
            align-items: center;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .qrcode-image {
            width: 200px;
            height: 200px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .download-button {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #4caf50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .download-button:hover {
            background-color: #45a049;
        }

        h1 {
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
    <script>
        function downloadImage(imageUrl, imageName) {
            var link = document.createElement('a');
            link.href = imageUrl;
            link.download = imageName + '.png';
            link.click();
        }
    </script>
</head>
<body>
<h1>Meal Coupon</h1>
<div class="container">
    @foreach ($mealCoupons as $coupon)
        <div>
            <img src="{{ route('qrcode', $coupon->unique_code) }}" alt="Meal Coupon QR Code" class="qrcode-image">
            <br>
            <button onclick="downloadImage('{{ route('qrcode', $coupon->unique_code) }}', '{{ $coupon->unique_code }}')" class="download-button">Download</button>
        </div>
    @endforeach
</div>
</body>
</html>
