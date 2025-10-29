# Repair Records - Reports & Analytics System

## Overview
The Reports & Analytics system provides comprehensive analysis of repair records with filtering, statistics, and export capabilities.

## Features

### 📊 Analytics Dashboard
- **Summary Statistics**: Total records, loss time, NG records, and averages
- **Loss Time by Issue**: Breakdown of downtime by reject/issue type
- **Loss Time by Time Period**: Daily, weekly, monthly, or yearly analysis
- **Detailed Records**: Complete record listing with filtering

### 🔍 Filtering Options
- **Date Range**: Custom from/to date selection
- **Time Period**: Daily, Weekly, Monthly, or Yearly grouping
- **Issue Filter**: All issues or specific issue filtering
- **Status Filter**: Pending, NG, Tech Verified, Line Verified, or Approved

### 📈 Loss Time Analytics
The system calculates and displays:
- Total loss time per issue/reject type
- Average loss time per issue
- Loss time trends over different time periods
- Summary statistics with visual indicators

### 📤 Export Options

#### CSV Export (Available Now)
- Complete record data with all fields
- Summary statistics included
- UTF-8 encoded for proper character support
- Can be opened in Excel or any spreadsheet application

#### Excel Export (Requires Composer Setup)
For advanced Excel formatting with charts and styling:

1. **Install Composer**:
   ```bash
   # Download from https://getcomposer.org/download/
   # Or use the installation script: install_composer.php
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   ```

3. **Features**:
   - Formatted Excel files (.xlsx)
   - Charts and visualizations
   - Multiple sheets (Summary, Details, Analytics)
   - Professional styling and formatting

## Usage

### Accessing Reports
1. Go to the main records page (`index.php`)
2. Click the "Export" button (download icon)
3. You'll be redirected to the Reports page (`reports.php`)

### Generating Reports
1. **Set Filters**:
   - Choose your date range
   - Select time period (daily/weekly/monthly/yearly)
   - Filter by specific issues if needed
   - Choose status filter if required

2. **Generate Report**:
   - Click "Generate Report" button
   - Wait for data to load
   - Review the analytics tables

3. **Export Data**:
   - Click "Export to CSV/Excel" for immediate download
   - CSV files work in Excel and provide full functionality

### Understanding the Analytics

#### Summary Statistics
- **Total Records**: Count of records matching your filters
- **Total Loss Time**: Sum of all loss time in minutes
- **NG Records**: Count of records marked as "No Good"
- **Avg Loss Time**: Average loss time per record

#### Loss Time by Issue
Shows breakdown by reject/issue type:
- Issue name
- Number of occurrences
- Total loss time for that issue
- Average loss time per occurrence

#### Loss Time by Time Period
Groups data by your selected time period:
- Period identifier (date, week, month, or year)
- Number of records in that period
- Total loss time for that period
- Average loss time per record in that period

## Technical Details

### Loss Time Calculation
The system handles multiple loss time formats:
- "120 mins" → 120 minutes
- "2h 30m" → 150 minutes  
- "2:30" → 150 minutes
- "30" → 30 minutes

### Database Queries
- Optimized queries with proper indexing
- Prepared statements for security
- Efficient filtering and grouping

### Performance
- Pagination for large datasets
- AJAX loading for smooth user experience
- Caching of frequently accessed data

## File Structure
```
├── reports.php              # Main reports page
├── api.php                  # Backend API endpoints
├── install_composer.php     # Composer setup helper
├── composer.json            # PHP dependencies
└── README_REPORTS.md        # This documentation
```

## API Endpoints

### GET /api.php?action=get_report_data
Returns report data based on filters:
- Parameters: dateFrom, dateTo, timePeriod, rejectFilter, specificIssue, statusFilter
- Response: JSON with summary, analytics, and detailed records

### POST /api.php?action=export_excel
Exports data as CSV file:
- Parameters: Same as get_report_data
- Response: CSV file download

## Troubleshooting

### CSV Export Issues
- Ensure proper file permissions
- Check PHP memory limits for large datasets
- Verify UTF-8 encoding for special characters

### Composer Setup Issues
- Ensure PHP 7.4+ is installed
- Check internet connection for package downloads
- Verify Composer installation path

### Performance Issues
- Use date filters to limit dataset size
- Consider pagination for very large reports
- Monitor database query performance

## Future Enhancements
- PDF export with charts
- Email report scheduling
- Advanced chart visualizations
- Custom report templates
- Real-time dashboard updates

## Support
For issues or questions about the reporting system, check:
1. This documentation
2. Browser console for JavaScript errors
3. PHP error logs for backend issues
4. Database connection and permissions
