<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AI Document Repository - GW05</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        /* Main Card */
        .dashboard-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease-out;
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

        /* Header Section */
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            text-align: center;
            color: white;
        }

        .header-section h1 {
            font-size: 36px;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .header-section p {
            font-size: 18px;
            opacity: 0.9;
        }

        .group-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 50px;
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
        }

        /* Content Section */
        .content-section {
            padding: 40px;
        }

        /* Image Gallery */
        .image-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .image-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .image-gallery {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .project-image {
            width: 200px;
            height: 200px;
            border-radius: 15px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s;
            cursor: pointer;
        }

        .project-image:hover {
            transform: scale(1.05);
        }

        .placeholder-image {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .placeholder-image span {
            font-size: 14px;
            margin-top: 10px;
        }

        /* Video Section */
        .video-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .video-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Project Info */
        .project-info {
            background: #f7f9fc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }

        .project-info h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .project-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .group-name {
            font-size: 20px;
            color: #764ba2;
            font-weight: bold;
        }

        .members {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .member {
            background: white;
            padding: 5px 15px;
            border-radius: 20px;
            color: #667eea;
            font-weight: 500;
        }

        /* Button */
        .next-button {
            text-align: center;
            margin-top: 20px;
        }

        .btn-next {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-next:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* Footer */
        .footer {
            background: #f7f9fc;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        /* Modal for full image */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 24px;
            }
            .content-section {
                padding: 20px;
            }
            .project-name {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <!-- Header Section -->
            <div class="header-section">
                <h1>📚 AI DOCUMENT REPOSITORY</h1>
                <p>Using ABR, TBR & CBR Techniques</p>
                <div class="group-badge">
                    🎓 GROUP GW05 | BITP33S3 MULTIMEDIA DATABASE
                </div>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <!-- Image Section -->
                <div class="image-section">
                    <h2>
                        <span>📸</span> PROJECT GALLERY
                        <span>📸</span>
                    </h2>
                    <div class="image-gallery">
                        <!-- Image 1 - Database Icon -->
                        <div class="placeholder-image" onclick="openModal(this)">
                            🗄️
                            <span>Database</span>
                        </div>
                        <!-- Image 2 - AI Icon -->
                        <div class="placeholder-image" onclick="openModal(this)">
                            🤖
                            <span>Artificial Intelligence</span>
                        </div>
                        <!-- Image 3 - Document Icon -->
                        <div class="placeholder-image" onclick="openModal(this)">
                            📄
                            <span>Documents</span>
                        </div>
                        <!-- Image 4 - Search Icon -->
                        <div class="placeholder-image" onclick="openModal(this)">
                            🔍
                            <span>Smart Search</span>
                        </div>
                    </div>
                    <p style="margin-top: 15px; color: #666; font-size: 12px;">💡 Click on any image to enlarge</p>
                </div>

                <!-- Video Section -->
                <div class="video-section">
                    <h2>
                        <span>🎥</span> PROJECT PRESENTATION VIDEO
                        <span>🎥</span>
                    </h2>
                    <div class="video-container">
                        <iframe 
                            src="https://www.youtube.com/embed/dQw4w9WgXcQ" 
                            title="Project Presentation Video"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                    <p style="margin-top: 10px; color: #666; font-size: 12px;">🎬 Project demo and explanation video</p>
                </div>

                <!-- Project Information -->
                <div class="project-info">
                    <h3>📋 PROJECT DETAILS</h3>
                    <div class="project-name">
                        AI Document Repository System
                    </div>
                    <div class="group-name">
                        GROUP GW05
                    </div>
                    <div class="members">
                        <span class="member">👨‍💻 Ahmad</span>
                        <span class="member">👩‍💻 Farah</span>
                        <span class="member">👨‍💻 Sarah</span>
                        <span class="member">👩‍💻 John</span>
                    </div>
                    <p style="margin-top: 15px; color: #666;">
                        🚀 A smart document retrieval system using <strong>ABR</strong> (Attribute-Based Retrieval), 
                        <strong>TBR</strong> (Text-Based Retrieval), and <strong>CBR</strong> (Content-Based Retrieval) techniques.
                    </p>
                </div>

                <!-- Next Button -->
                <div class="next-button">
                    <a href="index.php" class="btn-next">
                        🚀 ENTER SYSTEM
                        <span>→</span>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>© 2024 GROUP GW05 | BITP33S3 Multimedia Database | All Rights Reserved</p>
            </div>
        </div>
    </div>

    <!-- Modal for full image -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <img id="modalImage" src="" alt="Full size image">
    </div>

    <script>
        // Open modal with full image
        function openModal(element) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            
            // Get the content of the placeholder
            const icon = element.innerHTML;
            
            // Create a canvas to convert emoji/text to image (simulated)
            // For actual images, you would use real image URLs
            modal.style.display = 'flex';
            
            // Since we're using emoji placeholders, we'll show a styled div in modal
            modal.innerHTML = `
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                            width: 400px; 
                            height: 400px; 
                            border-radius: 20px; 
                            display: flex; 
                            flex-direction: column; 
                            align-items: center; 
                            justify-content: center;
                            font-size: 100px;
                            color: white;">
                    ${icon}
                </div>
            `;
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
            // Reset modal content
            document.getElementById('imageModal').innerHTML = '<img id="modalImage" src="" alt="Full size image">';
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>