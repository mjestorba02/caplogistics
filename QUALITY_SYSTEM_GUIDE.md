# Asset Quality System - Quick Guide

## How Asset Quality Works

### Basic Quality Degradation
Assets lose quality over time based on their purchase date and lifespan:
- **New Asset**: 100% quality
- **Age Formula**: `Quality = 100 - (Days Old / Total Lifespan Days) × 100`

**Example:**
- Asset purchased: January 1, 2023
- Lifespan: 5 years = 1,825 days
- Today: January 1, 2024 (365 days old)
- Quality: 100 - (365/1,825 × 100) = **80%**

### Quality Improvement Through Maintenance
When you complete maintenance on an asset, its quality improves:
- Each completed maintenance increases quality by 10%
- Quality cannot exceed 100%

**Process:**
1. Go to **Asset Management → Asset Maintenance**
2. Click "Schedule Maintenance"
3. Fill in Asset ID, Name, Type, Date, Notes
4. Click "Schedule"
5. Once maintenance is done, click "Edit" on the record
6. Change Status to "Completed"
7. Click "Update"
8. **Asset quality automatically improves by 10%**

### Quality Visualization
In the Asset Management table, quality is shown as a progress bar:
- **Green (>70%)** - Asset in good condition
- **Yellow (40-70%)** - Asset needs maintenance soon
- **Red (<40%)** - Asset needs urgent maintenance

### Real-World Scenario

**Asset: Dell Desktop Computer**
- **Purchase Date:** January 1, 2023
- **Lifespan:** 5 years
- **Today's Date:** January 1, 2024

| Date | Days Old | Age % | Base Quality | Times Maintained | Quality Multiplier | Final Quality |
|------|----------|-------|--------------|------------------|-------------------|-----------------|
| Jan 1, 2023 | 0 | 0% | 100% | 0 | 1.0 | **100%** ✅ |
| Jan 1, 2024 | 365 | 20% | 80% | 0 | 1.0 | **80%** 🟡 |
| Jan 1, 2024 | 365 | 20% | 80% | 1 (after maint.) | 1.0* | **80%** 🟡 |
| After Maint 2 | 365 | 20% | 80% | 2 | 1.0* | **80%** ✅ |

*Note: Quality multiplier is capped at 1.0 (100%), so it can't go beyond the base quality

### Quality Maintenance Tips
1. **Schedule Regular Maintenance** - Before quality drops below 50%
2. **Document Work** - Add notes about what was maintained
3. **Track Dates** - Log when maintenance was actually completed
4. **Plan Replacements** - Assets with <30% quality should be retired soon

### Database Storage
Quality multipliers and maintenance dates are stored:
- **Table:** `asset_management`
- **Columns:**
  - `quality_multiplier` (0.0-1.0) - Impact of maintenance
  - `last_maintenance_date` - When last maintenance was completed
  - `lifespan_years` - Expected years of useful life
  - `purchase_date` - When asset was acquired

### Calculations (Backend)
```sql
SELECT 
    item_number,
    quality_percent = ROUND(
        (100 - ((TIMESTAMPDIFF(DAY, purchase_date, CURDATE()) 
                / (lifespan_years * 365)) * 100)) 
        * COALESCE(quality_multiplier, 1.0)
    )
FROM asset_management
```

### Viewing Maintenance Records
1. Go to **Asset Management → Asset Maintenance**
2. See all maintenance records with:
   - Asset ID and Name
   - Maintenance Type (Preventive, Corrective, Predictive)
   - Scheduled/Completed Date
   - Current Status
   - Notes about the work done

3. Filter by:
   - Status (Scheduled, Completed, Cancelled)
   - Type (Preventive, Corrective, Predictive)
   - Date
