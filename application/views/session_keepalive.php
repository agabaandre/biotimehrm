<!-- Session Keep Alive Script -->
<script>
(function() {
    'use strict';
    
    // Session keep-alive configuration
    const SESSION_CHECK_INTERVAL = 5 * 60 * 1000; // Check every 5 minutes
    const SESSION_EXTEND_INTERVAL = 2 * 60 * 1000; // Extend every 2 minutes during activity
    const INACTIVITY_TIMEOUT = 10 * 60 * 1000; // 10 minutes of inactivity
    
    let lastActivity = Date.now();
    let sessionCheckTimer = null;
    let sessionExtendTimer = null;
    let inactivityTimer = null;
    
    // Track user activity
    function updateActivity() {
        lastActivity = Date.now();
        resetInactivityTimer();
    }
    
    // Reset inactivity timer
    function resetInactivityTimer() {
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
        inactivityTimer = setTimeout(handleInactivity, INACTIVITY_TIMEOUT);
    }
    
    // Handle user inactivity
    function handleInactivity() {
        console.log('User inactive for', INACTIVITY_TIMEOUT / 1000, 'seconds');
        // You can add additional logic here for inactivity handling
    }
    
    // Check session status
    function checkSession() {
        fetch('<?php echo base_url("auth/checkSession"); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'expired') {
                // Session expired, redirect to login
                window.location.href = '<?php echo base_url("auth"); ?>';
            } else if (data.status === 'active') {
                console.log('Session is active, expires in', data.expires_in, 'seconds');
            }
        })
        .catch(error => {
            console.error('Session check failed:', error);
        });
    }
    
    // Extend session
    function extendSession() {
        fetch('<?php echo base_url("auth/extendSession"); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Session extended successfully');
            }
        })
        .catch(error => {
            console.error('Session extension failed:', error);
        });
    }
    
    // Initialize session keep-alive
    function initSessionKeepAlive() {
        // Set up activity tracking
        const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        activityEvents.forEach(event => {
            document.addEventListener(event, updateActivity, true);
        });
        
        // Set up timers
        sessionCheckTimer = setInterval(checkSession, SESSION_CHECK_INTERVAL);
        sessionExtendTimer = setInterval(extendSession, SESSION_EXTEND_INTERVAL);
        
        // Start inactivity timer
        resetInactivityTimer();
        
        // Check session immediately
        checkSession();
        
        console.log('Session keep-alive initialized');
    }
    
    // Clean up timers
    function cleanup() {
        if (sessionCheckTimer) {
            clearInterval(sessionCheckTimer);
        }
        if (sessionExtendTimer) {
            clearInterval(sessionExtendTimer);
        }
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSessionKeepAlive);
    } else {
        initSessionKeepAlive();
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', cleanup);
    
    // Handle visibility change (tab switching)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, pause timers
            cleanup();
        } else {
            // Page is visible, resume timers
            updateActivity();
            initSessionKeepAlive();
        }
    });
    
    // Expose functions for manual control
    window.SessionKeepAlive = {
        checkSession: checkSession,
        extendSession: extendSession,
        updateActivity: updateActivity,
        cleanup: cleanup
    };
    
})();
</script>
