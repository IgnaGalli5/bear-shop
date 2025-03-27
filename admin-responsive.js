/**
 * Bear Shop Admin Responsive JavaScript
 * This script adds responsive functionality to the admin panel
 */

document.addEventListener('DOMContentLoaded', function() {
    // Make tables responsive
    makeTablesResponsive();
    
    // Initialize mobile menu
    initMobileMenu();
    
    // Initialize floating action button
    initFloatingActionButton();
    
    // Fix iOS input zoom
    fixIOSInputZoom();
    
    // Handle modals on small screens
    handleModals();
  });
  
  /**
   * Makes tables responsive by adding data-label attributes
   */
  function makeTablesResponsive() {
    const tables = document.querySelectorAll('.responsive-table');
    
    tables.forEach(table => {
      const headerCells = table.querySelectorAll('thead th');
      const headerLabels = Array.from(headerCells).map(cell => cell.textContent.trim());
      
      const bodyRows = table.querySelectorAll('tbody tr');
      
      bodyRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        
        cells.forEach((cell, index) => {
          if (index < headerLabels.length) {
            cell.setAttribute('data-label', headerLabels[index]);
          }
        });
      });
    });
  }
  
  /**
   * Initializes the mobile menu
   */
  function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    const closeButton = document.querySelector('.mobile-menu-close');
    
    if (menuToggle && mobileMenu) {
      menuToggle.addEventListener('click', function() {
        mobileMenu.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
      });
      
      if (closeButton) {
        closeButton.addEventListener('click', function() {
          mobileMenu.classList.remove('active');
          document.body.style.overflow = ''; // Restore scrolling
        });
      }
      
      // Close menu when clicking outside
      document.addEventListener('click', function(event) {
        if (mobileMenu.classList.contains('active') && 
            !event.target.closest('.mobile-menu') && 
            !event.target.closest('.mobile-menu-toggle')) {
          mobileMenu.classList.remove('active');
          document.body.style.overflow = '';
        }
      });
      
      // Close menu when clicking on a menu item
      const menuLinks = mobileMenu.querySelectorAll('a');
      menuLinks.forEach(link => {
        link.addEventListener('click', function() {
          mobileMenu.classList.remove('active');
          document.body.style.overflow = '';
        });
      });
    }
  }
  
  /**
   * Initializes the floating action button
   */
  function initFloatingActionButton() {
    const fab = document.querySelector('.mobile-fab');
    
    if (fab) {
      fab.addEventListener('click', function() {
        // If there's a data-href attribute, navigate to that URL
        const href = this.getAttribute('data-href');
        if (href) {
          window.location.href = href;
        }
        
        // If there's a data-target attribute, show that element
        const target = this.getAttribute('data-target');
        if (target) {
          const targetElement = document.querySelector(target);
          if (targetElement) {
            targetElement.style.display = 'block';
          }
        }
      });
    }
  }
  
  /**
   * Prevents iOS from zooming when focusing on inputs
   */
  function fixIOSInputZoom() {
    // iOS zooms on input focus with font-size less than 16px
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        // Ensure font size is at least 16px when focused
        const fontSize = window.getComputedStyle(this).fontSize;
        if (parseFloat(fontSize) < 16) {
          this.style.fontSize = '16px';
        }
      });
      
      input.addEventListener('blur', function() {
        // Reset to original font size when blurred
        this.style.fontSize = '';
      });
    });
  }
  
  /**
   * Handles modals on small screens
   */
  function handleModals() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
      // Make sure modals are properly sized on mobile
      const resizeModal = function() {
        if (window.innerWidth <= 768) {
          const modalContent = modal.querySelector('.modal-content');
          if (modalContent) {
            modalContent.style.maxHeight = (window.innerHeight * 0.9) + 'px';
            modalContent.style.overflow = 'auto';
          }
        }
      };
      
      // Resize when modal is shown
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.attributeName === 'style' && 
              modal.style.display === 'block') {
            resizeModal();
          }
        });
      });
      
      observer.observe(modal, { attributes: true });
      
      // Also resize on window resize
      window.addEventListener('resize', function() {
        if (modal.style.display === 'block') {
          resizeModal();
        }
      });
    });
  }