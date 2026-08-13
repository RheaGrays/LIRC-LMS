    <style>
        .hero-pattern::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url('/bg.jpg');
            background-size: cover;
            background-position: center;
            opacity: 0.30;
            z-index: 0;
        }
        
        .fade-in-up {
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .field-slide-enter {
            animation: fieldSlideIn 0.25s ease-out;
        }

        @keyframes fieldSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
