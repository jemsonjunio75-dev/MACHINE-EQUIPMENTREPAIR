const fs = require('fs');
const path = require('path');

// Function to copy file
function copyFile(source, destination) {
    try {
        fs.copyFileSync(source, destination);
        console.log(`✓ Copied: ${path.basename(source)}`);
    } catch (error) {
        console.error(`✗ Failed to copy ${source}:`, error.message);
    }
}

// Ensure js directory exists
const jsDir = path.join(__dirname, 'js');
if (!fs.existsSync(jsDir)) {
    fs.mkdirSync(jsDir);
    console.log('✓ Created js directory');
}

// Copy Chart.js files
const chartJsPath = path.join(__dirname, 'node_modules', 'chart.js', 'dist', 'chart.umd.min.js');
const datalabelsPath = path.join(__dirname, 'node_modules', 'chartjs-plugin-datalabels', 'dist', 'chartjs-plugin-datalabels.min.js');

copyFile(chartJsPath, path.join(jsDir, 'chart.min.js'));
copyFile(datalabelsPath, path.join(jsDir, 'chartjs-plugin-datalabels.min.js'));

console.log('\n✓ Chart.js files updated successfully!');
console.log('You can now use the local files instead of CDN.');
