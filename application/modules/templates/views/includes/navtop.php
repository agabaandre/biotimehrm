<!-- Navbar - Modern Design v2.0 -->
<nav class="main-header navbar navbar-expand navbar-dark modern-navbar">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link modern-nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <div class="header-title">
        <?php if (!empty($uptitle)) {
          echo urldecode($uptitle);
        } ?>
      </div>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item facility-info">
      <div class="facility-display">
        <?php if (isset($_SESSION['district'])) {
          echo '<span class="district-name">' . $_SESSION['district'] . '</span>';
        } ?>
        <?php if (isset($_SESSION['facility_name'])) {
          echo '<span class="facility-name">' . $_SESSION['facility_name'] . '</span>';
        } ?>
      </div>
    </li>
    
    <?php if (in_array('13', $permissions)) { ?>
      <li class="nav-item">
        <a class="btn-modern nav-link" data-toggle="modal" data-target="#switch" style="cursor: pointer;">
          <i class="fas fa-toggle-on" style="font-size: 0.75rem; margin-right: 0.3rem;"></i>
          <span class="hidden-mobile">Change Facility</span>
        </a>
      </li>
    <?php } ?>
    
    <li class="nav-item dropdown user-dropdown">
      <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle user-profile">
        <div class="user-info">
          <span class="user-name"><?php echo $userdata['names']; ?></span>
          <img src="<?php echo base_url(); ?>assets/img/user.jpg" alt="User" class="user-avatar" />
        </div>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right modern-dropdown">
        <div class="dropdown-header">
          <i class="fas fa-user-circle"></i>
          <span>User Menu</span>
        </div>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item" data-toggle="modal" data-target="#profile">
          <i class="fas fa-user"></i> Profile
        </a>
        <div class="dropdown-divider"></div>
        <a href="<?php echo base_url(); ?>auth/logout" class="dropdown-item">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </li>
  </ul>
</nav>

<style>
/* Force modern styling with !important to override any existing styles */
.modern-navbar {
  background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important;
  box-shadow: 0 4px 20px rgba(0, 86, 98, 0.3) !important;
  border: none !important;
  padding: 0.75rem 1.5rem !important;
  transition: all 0.3s ease !important;
  color: inherit !important;
  text-align: center !important;
}

.modern-navbar:hover {
  box-shadow: 0 6px 25px rgba(0, 86, 98, 0.4);
}

.modern-nav-link {
  color: rgba(255, 255, 255, 0.9) !important;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.3s ease;
  margin-right: 0.5rem;
}

.modern-nav-link:hover {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-1px);
}

.header-title {
  color: #ffffff;
  font-size: 1.1rem;
  font-weight: 600;
  margin-top: 0.25rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.facility-info {
  margin-right: 0.75rem;
}

.facility-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.2rem 0.5rem;
  background: transparent;
  border-radius: 0;
  border: none;
  backdrop-filter: none;
  transition: all 0.3s ease;
  width: auto;
  min-height: auto;
  justify-content: center;
  text-overflow: ellipsis;
}

.facility-display:hover {
  background: transparent;
  transform: none;
  box-shadow: none;
}

.district-name {
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 400;
  opacity: 0.85;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: center;
}

.facility-name {
  color: #ffffff;
  font-size: 0.8rem;
  font-weight: 500;
  margin-top: 0.15rem;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: center;
}

.btn-modern {
  border-radius: 6px;
  padding: 0.2rem 0.5rem;
  font-weight: 400;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: transparent;
  transition: all 0.3s ease;
  backdrop-filter: none;
  width: auto;
  height: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #ffffff !important;
  font-size: 0.8rem;
  text-decoration: none;
}

.btn-modern:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.5);
  opacity: 1;
  text-decoration: none;
}

.user-dropdown {
  margin-left: 1rem;
}

.user-profile {
  padding: 0.4rem 0.8rem;
  border-radius: 10px;
  transition: all 0.3s ease;
  width: 180px;
  height: 55px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.user-profile:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-1px);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-name {
  color: #ffffff;
  font-weight: 500;
  font-size: 0.9rem;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.user-avatar:hover {
  border-color: rgba(255, 255, 255, 0.6);
  transform: scale(1.05);
}

.modern-dropdown {
  border: none;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  padding: 0.5rem 0;
  margin-top: 0.5rem;
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
}

.dropdown-header {
  padding: 0.75rem 1.5rem;
  color: #005662;
  font-weight: 600;
  font-size: 0.9rem;
  border-bottom: 1px solid rgba(0, 86, 98, 0.1);
}

.dropdown-header i {
  margin-right: 0.5rem;
  color: #20c198;
}

.dropdown-item {
  padding: 0.75rem 1.5rem;
  color: #2c3e50;
  font-weight: 500;
  transition: all 0.3s ease;
  border-radius: 8px;
  margin: 0.25rem 0.5rem;
}

.dropdown-item:hover {
  background: rgba(0, 86, 98, 0.1);
  color: #005662;
  transform: translateX(5px);
}

.dropdown-item i {
  margin-right: 0.75rem;
  color: #20c198;
  width: 16px;
}

@media (max-width: 768px) {
  .modern-navbar {
    padding: 0.5rem 1rem !important;
  }
  
  .facility-info {
    margin-right: 0.5rem !important;
  }
  
  .facility-display {
    padding: 0.15rem 0.3rem !important;
    width: auto !important;
    min-height: auto !important;
  }
  
  .btn-modern {
    width: auto !important;
    height: auto !important;
    padding: 0.15rem 0.3rem !important;
  }
  
  .user-profile {
    width: 160px !important;
    height: 45px !important;
  }
  
  .district-name, .facility-name {
    font-size: 0.75rem !important;
  }
  
  .user-name {
    max-width: 100px !important;
  }
}

/* Additional overrides to ensure modern styling */
.main-header.navbar {
  background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important;
}

.navbar-dark {
  background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important;
}

/* Ensure the navigation is visible and properly styled */
.navbar-nav .nav-link {
  color: rgba(255, 255, 255, 0.9) !important;
}

.navbar-nav .nav-link:hover {
  color: #ffffff !important;
}

/* Force override any existing styles */
nav.main-header.navbar.navbar-expand.navbar-dark,
nav.main-header.navbar.navbar-expand.navbar-dark.modern-navbar {
  background: linear-gradient(135deg, #005662 0%, #20c198 100%) !important;
  box-shadow: 0 4px 20px rgba(0, 86, 98, 0.3) !important;
  border: none !important;
  padding: 0.75rem 1.5rem !important;
  transition: all 0.3s ease !important;
}
</style>

<script>
// Force apply modern styling
document.addEventListener('DOMContentLoaded', function() {
  // Ensure the navbar has the modern class
  const navbar = document.querySelector('.main-header.navbar');
  if (navbar) {
    navbar.classList.add('modern-navbar');
    
    // Force apply the gradient background
    navbar.style.background = 'linear-gradient(135deg, #005662 0%, #20c198 100%)';
    navbar.style.boxShadow = '0 4px 20px rgba(0, 86, 98, 0.3)';
    navbar.style.border = 'none';
    navbar.style.padding = '0.75rem 1.5rem';
    navbar.style.transition = 'all 0.3s ease';
    
    console.log('Modern navbar styling applied');
  }
  
  // Apply modern styling to all nav links
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  navLinks.forEach(link => {
    link.classList.add('modern-nav-link');
  });
});
</script>
<!-- /.navbar -->