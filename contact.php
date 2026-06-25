<?php
session_start();

/* =========================
   DATABASE CONFIGURATION
========================= */
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "science_park_db";

/* =========================
   FLASH MESSAGE
========================= */
$success = $_SESSION["success"] ?? "";
$error   = $_SESSION["error"] ?? "";
$old     = $_SESSION["old"] ?? [];

unset($_SESSION["success"], $_SESSION["error"], $_SESSION["old"]);

function clean_input($data) {
    return trim(htmlspecialchars($data ?? "", ENT_QUOTES, "UTF-8"));
}

function old_value($key, $old) {
    return htmlspecialchars($old[$key] ?? "", ENT_QUOTES, "UTF-8");
}

function selected_value($key, $value, $old) {
    return (($old[$key] ?? "") === $value) ? "selected" : "";
}

/* =========================
   FORM SUBMIT BACKEND
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name           = clean_input($_POST["full_name"] ?? "");
    $school_name         = clean_input($_POST["school_name"] ?? "");
    $phone               = clean_input($_POST["phone"] ?? "");
    $email               = clean_input($_POST["email"] ?? "");
    $city_state          = clean_input($_POST["city_state"] ?? "");
    $project_type        = clean_input($_POST["project_type"] ?? "");
    $interested_category = clean_input($_POST["interested_category"] ?? "");
    $budget              = clean_input($_POST["budget"] ?? "");
    $message             = clean_input($_POST["message"] ?? "");

    $_SESSION["old"] = [
        "full_name" => $full_name,
        "school_name" => $school_name,
        "phone" => $phone,
        "email" => $email,
        "city_state" => $city_state,
        "project_type" => $project_type,
        "interested_category" => $interested_category,
        "budget" => $budget,
        "message" => $message
    ];

    if (
        empty($full_name) ||
        empty($school_name) ||
        empty($phone) ||
        empty($city_state) ||
        empty($project_type) ||
        empty($message)
    ) {
        $_SESSION["error"] = "Please fill all required fields.";
        header("Location: contact.php#contactForm");
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Please enter a valid email address.";
        header("Location: contact.php#contactForm");
        exit;
    }

    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        $conn->set_charset("utf8mb4");

        $stmt = $conn->prepare("
            INSERT INTO quote_enquiries
            (full_name, school_name, phone, email, city_state, project_type, interested_category, budget, message)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssssss",
            $full_name,
            $school_name,
            $phone,
            $email,
            $city_state,
            $project_type,
            $interested_category,
            $budget,
            $message
        );

        $stmt->execute();

        $stmt->close();
        $conn->close();

        unset($_SESSION["old"]);
        $_SESSION["success"] = "Thank you! Your enquiry has been submitted successfully.";
        header("Location: contact.php#contactForm");
        exit;

    } catch (Exception $e) {
        $_SESSION["error"] = "Database error. Please check your database connection or SQL import.";
        header("Location: contact.php#contactForm");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Contact Science Park | Get Quote for Outdoor Science Gadgets</title>
  <meta name="description" content="Contact Science Park for outdoor science park gadgets, school science models, educational installations, project consultation and quotation." />
  <meta name="keywords" content="contact science park, science park quote, outdoor science gadgets, school science models, science park equipment supplier" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

  <style>
    :root {
      --blue-deep: #0d1b3e;
      --blue-primary: #1a4f8a;
      --blue-medium: #256bb5;
      --blue-light: #e8f2fb;
      --green-primary: #2d8c4e;
      --green-light: #e6f7ee;
      --yellow-accent: #f4a920;
      --white: #ffffff;
      --gray-50: #f9fafb;
      --gray-200: #e5e7eb;
      --gray-600: #6b7280;
      --gray-800: #1f2937;
      --shadow-md: 0 10px 28px rgba(13, 27, 62, 0.09);
      --shadow-xl: 0 24px 58px rgba(13, 27, 62, 0.16);
      --radius-xl: 34px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Segoe UI", Arial, sans-serif;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background: #ffffff;
      color: var(--gray-800);
      line-height: 1.7;
      overflow-x: hidden;
    }

    a {
      text-decoration: none;
    }

    #mainNavbar {
      background: rgba(255, 255, 255, 0.94);
      backdrop-filter: blur(18px);
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
      padding: 10px 0;
      z-index: 1050;
      transition: 0.3s ease;
    }

    #mainNavbar.scrolled {
      box-shadow: var(--shadow-md);
      padding: 7px 0;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 800;
      font-size: 1.45rem;
      color: var(--blue-deep);
    }

    .navbar-brand:hover {
      color: var(--blue-primary);
    }

    .brand-icon {
      font-size: 1.6rem;
      color: var(--blue-medium);
    }

    .brand-text span {
      color: var(--green-primary);
    }

    .nav-link {
      font-weight: 600;
      color: var(--gray-800);
      margin: 0 4px;
      padding: 8px 14px !important;
      border-radius: 25px;
      transition: 0.3s ease;
      font-size: 0.95rem;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--blue-medium);
      background: var(--blue-light);
    }

    .btn-quote-nav {
      background: var(--yellow-accent);
      color: #fff !important;
      font-weight: 700;
      border-radius: 30px;
      padding: 10px 22px !important;
      box-shadow: 0 8px 22px rgba(244, 169, 32, 0.35);
      transition: 0.3s ease;
    }

    .btn-quote-nav:hover {
      background: #e89a10;
      transform: translateY(-2px);
    }

    .navbar-toggler {
      border: none;
      box-shadow: none !important;
    }

    section {
      padding: 85px 0;
    }

    .btn-main {
      background: var(--blue-medium);
      color: white;
      padding: 14px 30px;
      border-radius: 40px;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: 0.3s ease;
      box-shadow: 0 12px 28px rgba(37, 107, 181, 0.28);
      border: none;
    }

    .btn-main:hover {
      background: #1e5a9e;
      color: white;
      transform: translateY(-4px);
    }

    .btn-outline-main {
      border: 2px solid var(--blue-medium);
      color: var(--blue-medium);
      background: white;
      padding: 12px 30px;
      border-radius: 40px;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: 0.3s ease;
    }

    .btn-outline-main:hover {
      background: var(--blue-medium);
      color: white;
      transform: translateY(-4px);
    }

    .contact-hero {
      padding: 145px 0 105px;
      background:
        radial-gradient(circle at 12% 22%, rgba(37, 107, 181, 0.13), transparent 32%),
        radial-gradient(circle at 90% 70%, rgba(45, 140, 78, 0.15), transparent 34%),
        linear-gradient(135deg, #f0f7ff 0%, #ffffff 50%, #fff7e6 100%);
      position: relative;
      overflow: hidden;
    }

    .contact-hero::before {
      content: "";
      position: absolute;
      width: 380px;
      height: 380px;
      border-radius: 50%;
      background: rgba(244, 169, 32, 0.13);
      top: -130px;
      right: -110px;
    }

    .contact-hero::after {
      content: "";
      position: absolute;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: rgba(37, 107, 181, 0.10);
      left: -85px;
      bottom: -85px;
    }

    .hero-content,
    .hero-card-wrap {
      position: relative;
      z-index: 2;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: white;
      color: var(--blue-medium);
      padding: 10px 20px;
      border-radius: 40px;
      font-weight: 800;
      box-shadow: 0 8px 25px rgba(37, 107, 181, 0.12);
      margin-bottom: 20px;
    }

    .contact-hero h1 {
      font-size: clamp(40px, 5vw, 66px);
      font-weight: 900;
      line-height: 1.08;
      color: var(--blue-deep);
      margin-bottom: 18px;
    }

    .contact-hero h1 span {
      color: var(--green-primary);
    }

    .contact-hero p {
      max-width: 700px;
      font-size: 18px;
      color: var(--gray-600);
      margin-bottom: 30px;
    }

    .hero-actions {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }

    .hero-contact-card {
      background: #ffffff;
      border-radius: var(--radius-xl);
      padding: 34px;
      box-shadow: var(--shadow-xl);
      border: 1px solid rgba(37, 107, 181, 0.14);
      position: relative;
      overflow: hidden;
    }

    .hero-card-icon {
      width: 76px;
      height: 76px;
      border-radius: 26px;
      background: var(--blue-light);
      color: var(--blue-medium);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      margin-bottom: 20px;
    }

    .hero-contact-card h3 {
      font-weight: 900;
      color: var(--blue-deep);
      margin-bottom: 10px;
    }

    .hero-contact-card p {
      font-size: 15.5px;
      margin-bottom: 18px;
      color: var(--gray-600);
    }

    .hero-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .hero-list li {
      display: flex;
      gap: 12px;
      margin-bottom: 14px;
      font-weight: 700;
      color: var(--gray-800);
    }

    .hero-list i {
      color: var(--green-primary);
      margin-top: 4px;
    }

    .section-header {
      text-align: center;
      margin-bottom: 48px;
    }

    .section-tag {
      display: inline-block;
      background: var(--green-light);
      color: var(--green-primary);
      font-weight: 900;
      font-size: 13px;
      padding: 8px 18px;
      border-radius: 25px;
      letter-spacing: 0.6px;
      text-transform: uppercase;
      margin-bottom: 14px;
    }

    .section-title {
      font-size: clamp(32px, 4vw, 46px);
      font-weight: 900;
      color: var(--blue-deep);
      margin-bottom: 10px;
    }

    .section-subtitle {
      color: var(--gray-600);
      max-width: 760px;
      margin: 0 auto;
      font-size: 17px;
    }

    .contact-form-section {
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
      padding-top: 75px;
      padding-bottom: 95px;
    }

    .form-wrapper {
      background: #ffffff;
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-xl);
      border: 1px solid rgba(37, 107, 181, 0.14);
      overflow: hidden;
    }

    .form-left {
      padding: 44px;
    }

    .form-left h2 {
      font-weight: 900;
      color: var(--blue-deep);
      margin-bottom: 10px;
      font-size: clamp(30px, 4vw, 42px);
    }

    .form-left p {
      color: var(--gray-600);
      margin-bottom: 28px;
    }

    .form-label {
      font-weight: 800;
      color: var(--blue-deep);
      margin-bottom: 8px;
      font-size: 14px;
    }

    .form-control,
    .form-select {
      min-height: 54px;
      border-radius: 18px;
      border: 1.5px solid var(--gray-200);
      font-weight: 600;
      padding: 12px 16px;
    }

    textarea.form-control {
      min-height: 140px;
      resize: none;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--blue-medium);
      box-shadow: 0 0 0 4px rgba(37, 107, 181, 0.13);
    }

    .form-side {
      height: 100%;
      min-height: 100%;
      padding: 44px;
      color: #ffffff;
      background:
        linear-gradient(rgba(13, 27, 62, 0.82), rgba(13, 27, 62, 0.88)),
        url("https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=1000&auto=format&fit=crop");
      background-size: cover;
      background-position: center;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .form-side h3 {
      font-weight: 900;
      font-size: 30px;
      margin-bottom: 16px;
    }

    .form-side p {
      color: rgba(255,255,255,0.76);
    }

    .support-list {
      list-style: none;
      padding: 0;
      margin: 28px 0;
    }

    .support-list li {
      display: flex;
      gap: 12px;
      margin-bottom: 18px;
      font-weight: 700;
      color: rgba(255,255,255,0.92);
    }

    .support-list i {
      color: var(--yellow-accent);
      margin-top: 4px;
    }

    .footer-section {
      background: var(--blue-deep);
      color: rgba(255, 255, 255, 0.85);
      padding: 60px 0 0;
    }

    .footer-logo {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 1.4rem;
      font-weight: 800;
      color: #fff;
      text-decoration: none;
      margin-bottom: 14px;
    }

    .footer-logo:hover {
      color: var(--yellow-accent);
    }

    .footer-logo i {
      color: var(--yellow-accent);
    }

    .footer-brand p {
      font-size: 0.9rem;
      line-height: 1.7;
      color: rgba(255, 255, 255, 0.7);
    }

    .social-links {
      display: flex;
      gap: 10px;
      margin-top: 16px;
    }

    .social-links a {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      transition: 0.3s ease;
      font-size: 0.95rem;
    }

    .social-links a:hover {
      background: var(--yellow-accent);
      transform: translateY(-4px);
      color: #fff;
    }

    .footer-section h5 {
      color: #fff;
      font-weight: 800;
      margin-bottom: 16px;
      font-size: 1rem;
    }

    .footer-links,
    .footer-contact {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-links li {
      margin-bottom: 8px;
    }

    .footer-links a {
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      transition: 0.3s ease;
      font-size: 0.9rem;
    }

    .footer-links a:hover {
      color: var(--yellow-accent);
      padding-left: 6px;
    }

    .footer-contact li {
      margin-bottom: 10px;
      font-size: 0.88rem;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      color: rgba(255, 255, 255, 0.7);
    }

    .footer-contact i {
      color: var(--yellow-accent);
      flex-shrink: 0;
      margin-top: 3px;
    }

    .footer-divider {
      border-color: rgba(255, 255, 255, 0.15);
      margin: 30px 0 16px;
    }

    .footer-bottom {
      text-align: center;
      padding-bottom: 20px;
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.5);
    }

    .back-to-top {
      position: fixed;
      bottom: 28px;
      right: 28px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--blue-medium);
      color: #fff;
      border: none;
      cursor: pointer;
      z-index: 999;
      opacity: 0;
      visibility: hidden;
      transform: translateY(20px);
      transition: 0.3s ease;
      box-shadow: 0 8px 22px rgba(37, 107, 181, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .back-to-top.show {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    @media (max-width: 991px) {
      .navbar-collapse {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 18px;
        padding: 16px;
        margin-top: 10px;
        box-shadow: var(--shadow-xl);
      }

      .contact-hero {
        text-align: center;
        padding: 125px 0 85px;
      }

      .contact-hero p {
        margin-left: auto;
        margin-right: auto;
      }

      .hero-actions {
        justify-content: center;
      }

      .hero-contact-card {
        margin-top: 35px;
      }

      .form-side {
        min-height: 450px;
      }
    }

    @media (max-width: 576px) {
      section {
        padding: 60px 0;
      }

      .contact-hero h1 {
        font-size: 38px;
      }

      .hero-actions {
        flex-direction: column;
      }

      .btn-main,
      .btn-outline-main {
        width: 100%;
        justify-content: center;
      }

      .hero-contact-card,
      .form-left,
      .form-side {
        padding: 26px;
      }
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
    <div class="container">
      <a class="navbar-brand" href="index.html">
        <i class="fas fa-atom brand-icon"></i>
        <span class="brand-text">Science <span>Park</span></span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
          <li class="nav-item"><a class="nav-link" href="Products.html">Products</a></li>
          <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
          <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
            <a href="#contactForm" class="btn btn-quote-nav">Get Quote <i class="fas fa-arrow-right ms-1"></i></a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="contact-hero">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-7 hero-content">
          <span class="hero-badge">
            <i class="fa-solid fa-headset"></i>
            Contact & Project Enquiry
          </span>

          <h1>Let’s Build Your <span>Science Park</span></h1>

          <p>
            Contact us for outdoor science gadgets, school science park setup, product quotation,
            installation guidance, campus planning and customized educational model solutions.
          </p>

          <div class="hero-actions">
            <a href="#contactForm" class="btn-main">
              Send Enquiry <i class="fa-solid fa-paper-plane"></i>
            </a>
            <a href="tel:+91XXXXXXXXXX" class="btn-outline-main">
              Call Now <i class="fa-solid fa-phone"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-5 hero-card-wrap">
          <div class="hero-contact-card">
            <div class="hero-card-icon">
              <i class="fa-solid fa-school"></i>
            </div>

            <h3>For Schools & Institutions</h3>
            <p>
              Share your requirement and our team will suggest suitable science models
              according to class level, campus space and project budget.
            </p>

            <ul class="hero-list">
              <li><i class="fa-solid fa-check-circle"></i> Outdoor science park setup</li>
              <li><i class="fa-solid fa-check-circle"></i> Science model consultation</li>
              <li><i class="fa-solid fa-check-circle"></i> Product quotation support</li>
              <li><i class="fa-solid fa-check-circle"></i> Installation and guidance</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Form -->
  <section class="contact-form-section" id="contactForm">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Get Quote</span>
        <h2 class="section-title">Request a Quotation</h2>
        <p class="section-subtitle">
          Fill the form below and your enquiry will be saved in MySQL database.
        </p>
      </div>

      <div class="form-wrapper">
        <div class="row g-0">
          <div class="col-lg-7">
            <div class="form-left">
              <h2>Send Your Enquiry</h2>
              <p>Our team will contact you with suitable product details and quotation.</p>

              <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                  <i class="fa-solid fa-circle-check me-2"></i>
                  <?php echo htmlspecialchars($success); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                  <i class="fa-solid fa-triangle-exclamation me-2"></i>
                  <?php echo htmlspecialchars($error); ?>
                </div>
              <?php endif; ?>

              <form method="POST" action="contact.php#contactForm">
                <div class="row g-3">

                  <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="full_name" placeholder="Enter your name" value="<?php echo old_value('full_name', $old); ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">School / Organization Name *</label>
                    <input type="text" class="form-control" name="school_name" placeholder="Enter school name" value="<?php echo old_value('school_name', $old); ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Phone Number *</label>
                    <input type="tel" class="form-control" name="phone" placeholder="+91 XXXXX XXXXX" value="<?php echo old_value('phone', $old); ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="example@email.com" value="<?php echo old_value('email', $old); ?>">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">City / State *</label>
                    <input type="text" class="form-control" name="city_state" placeholder="Enter city/state" value="<?php echo old_value('city_state', $old); ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Project Type *</label>
                    <select class="form-select" name="project_type" required>
                      <option value="">Select project type</option>
                      <option value="New Science Park Setup" <?php echo selected_value('project_type', 'New Science Park Setup', $old); ?>>New Science Park Setup</option>
                      <option value="Science Gadget Purchase" <?php echo selected_value('project_type', 'Science Gadget Purchase', $old); ?>>Science Gadget Purchase</option>
                      <option value="School Campus Installation" <?php echo selected_value('project_type', 'School Campus Installation', $old); ?>>School Campus Installation</option>
                      <option value="Custom Educational Models" <?php echo selected_value('project_type', 'Custom Educational Models', $old); ?>>Custom Educational Models</option>
                      <option value="Maintenance / Support" <?php echo selected_value('project_type', 'Maintenance / Support', $old); ?>>Maintenance / Support</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Interested Category</label>
                    <select class="form-select" name="interested_category">
                      <option value="">Select category</option>
                      <option value="Mechanics Models" <?php echo selected_value('interested_category', 'Mechanics Models', $old); ?>>Mechanics Models</option>
                      <option value="Light Models" <?php echo selected_value('interested_category', 'Light Models', $old); ?>>Light Models</option>
                      <option value="Sound Models" <?php echo selected_value('interested_category', 'Sound Models', $old); ?>>Sound Models</option>
                      <option value="Energy Models" <?php echo selected_value('interested_category', 'Energy Models', $old); ?>>Energy Models</option>
                      <option value="Chemistry Models" <?php echo selected_value('interested_category', 'Chemistry Models', $old); ?>>Chemistry Models</option>
                      <option value="Biology Models" <?php echo selected_value('interested_category', 'Biology Models', $old); ?>>Biology Models</option>
                      <option value="Complete Science Park Package" <?php echo selected_value('interested_category', 'Complete Science Park Package', $old); ?>>Complete Science Park Package</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Approx Budget</label>
                    <select class="form-select" name="budget">
                      <option value="">Select budget range</option>
                      <option value="Below ₹1 Lakh" <?php echo selected_value('budget', 'Below ₹1 Lakh', $old); ?>>Below ₹1 Lakh</option>
                      <option value="₹1 Lakh - ₹3 Lakh" <?php echo selected_value('budget', '₹1 Lakh - ₹3 Lakh', $old); ?>>₹1 Lakh - ₹3 Lakh</option>
                      <option value="₹3 Lakh - ₹5 Lakh" <?php echo selected_value('budget', '₹3 Lakh - ₹5 Lakh', $old); ?>>₹3 Lakh - ₹5 Lakh</option>
                      <option value="₹5 Lakh - ₹10 Lakh" <?php echo selected_value('budget', '₹5 Lakh - ₹10 Lakh', $old); ?>>₹5 Lakh - ₹10 Lakh</option>
                      <option value="Above ₹10 Lakh" <?php echo selected_value('budget', 'Above ₹10 Lakh', $old); ?>>Above ₹10 Lakh</option>
                    </select>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Message / Requirement *</label>
                    <textarea class="form-control" name="message" placeholder="Write your requirement, number of models, available space, class level, or any custom need..." required><?php echo old_value('message', $old); ?></textarea>
                  </div>

                  <div class="col-12 mt-3">
                    <button type="submit" class="btn-main">
                      Submit Enquiry <i class="fa-solid fa-arrow-right"></i>
                    </button>
                  </div>

                </div>
              </form>

            </div>
          </div>

          <div class="col-lg-5">
            <div class="form-side">
              <div>
                <h3>Project Support Includes</h3>
                <p>
                  We help you choose practical, safe and curriculum-friendly science models
                  for your school campus.
                </p>

                <ul class="support-list">
                  <li><i class="fa-solid fa-circle-check"></i> Product selection guidance</li>
                  <li><i class="fa-solid fa-circle-check"></i> Campus space planning</li>
                  <li><i class="fa-solid fa-circle-check"></i> Model-wise quotation</li>
                  <li><i class="fa-solid fa-circle-check"></i> Installation and safety support</li>
                  <li><i class="fa-solid fa-circle-check"></i> School-friendly science concepts</li>
                </ul>
              </div>

              <div>
                <a href="products.html" class="btn-main">
                  View Products <i class="fa-solid fa-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="footer-brand">
            <a href="index.html" class="footer-logo">
              <i class="fas fa-atom"></i>
              Science Park
            </a>
            <p>
              Outdoor science learning gadgets for schools and educational institutions.
              Learn science through play-based practical models.
            </p>

            <div class="social-links">
              <a href="#"><i class="fab fa-facebook-f"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
              <a href="#"><i class="fab fa-youtube"></i></a>
              <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
          </div>
        </div>

        <div class="col-lg-2 col-md-6">
          <h5>Quick Links</h5>
          <ul class="footer-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="products.html">Products</a></li>
            <li><a href="benefits.html">Benefits</a></li>
            <li><a href="contact.php">Contact</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6">
          <h5>Products</h5>
          <ul class="footer-links">
            <li><a href="products.html">Science Gadgets</a></li>
            <li><a href="products.html">Mechanics Models</a></li>
            <li><a href="products.html">Sound & Light Models</a></li>
            <li><a href="products.html">Biology Models</a></li>
            <li><a href="products.html">Energy Models</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6">
          <h5>Contact Details</h5>
          <ul class="footer-contact">
            <li><i class="fas fa-phone"></i> +91 XXXXX XXXXX</li>
            <li><i class="fas fa-envelope"></i> info@example.com</li>
            <li><i class="fas fa-location-dot"></i> Your City, India</li>
          </ul>
        </div>
      </div>

      <hr class="footer-divider">

      <div class="footer-bottom">
        <p>© 2026 Science Park. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <button class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const navbar = document.getElementById("mainNavbar");
    const backToTop = document.getElementById("backToTop");

    window.addEventListener("scroll", () => {
      navbar.classList.toggle("scrolled", window.scrollY > 50);
      backToTop.classList.toggle("show", window.scrollY > 350);
    });

    backToTop.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  </script>

</body>
</html>