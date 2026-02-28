<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        button { padding: 10px 15px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-info { background-color: #17a2b8; color: white; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Session Management Test</h1>
    
    <div class="test-section info">
        <h3>Current Session Status</h3>
        <p>Use the buttons below to test session functionality:</p>
    </div>

    <div class="test-section">
        <h3>Test Session Endpoints</h3>
        <button class="btn-primary" onclick="testCheckSession()">Test checkSession</button>
        <button class="btn-success" onclick="testExtendSession()">Test extendSession</button>
        <button class="btn-info" onclick="testSessionStatus()">Test Session Status</button>
    </div>

    <div id="results" class="test-section" style="display: none;">
        <h3>Test Results</h3>
        <div id="resultContent"></div>
    </div>

    <script>
        function showResult(content, type = 'info') {
            const resultsDiv = document.getElementById('results');
            const contentDiv = document.getElementById('resultContent');
            
            contentDiv.innerHTML = content;
            resultsDiv.className = `test-section ${type}`;
            resultsDiv.style.display = 'block';
        }

        function testCheckSession() {
            fetch('<?php echo base_url("auth/checkSession"); ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const content = `
                    <h4>checkSession Response:</h4>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                showResult(content, data.status === 'active' ? 'success' : 'error');
            })
            .catch(error => {
                showResult(`<h4>Error:</h4><p>${error.message}</p>`, 'error');
            });
        }

        function testExtendSession() {
            fetch('<?php echo base_url("auth/extendSession"); ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const content = `
                    <h4>extendSession Response:</h4>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                showResult(content, data.status === 'success' ? 'success' : 'error');
            })
            .catch(error => {
                showResult(`<h4>Error:</h4><p>${error.message}</p>`, 'error');
            });
        }

        function testSessionStatus() {
            fetch('<?php echo base_url("auth/testSession"); ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const content = `
                    <h4>Session Status:</h4>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                showResult(content, 'info');
            })
            .catch(error => {
                showResult(`<h4>Error:</h4><p>${error.message}</p>`, 'error');
            });
        }

        // Auto-test on page load
        window.onload = function() {
            setTimeout(() => {
                testCheckSession();
            }, 1000);
        };
    </script>
</body>
</html>
