# Generic Issue Examples

This file contains multiple issue examples covering different types of work.

---

## Example 1: Bug Fix

### Enable validation for geolocation coordinates

**Problem**

User profiles can be saved with invalid coordinates (lat > 90, lng > 180), breaking map displays and causing query errors downstream.

**Current code:**
```php
// app/Services/UserService.php:145
$userData->location = [
    'lat' => $input['lat'],
    'lng' => $input['lng'],
]; // ❌ No validation before saving
```

**Context**

Valid coordinate ranges per WGS84 standard:
- Latitude: -90 to +90 (poles)
- Longitude: -180 to +180 (antimeridian)

Invalid coordinates cause:
- Map rendering failures (markers don't appear)
- Distance calculation errors (returns NaN)
- Geocoding API errors when reverse geocoding

**Acceptance Criteria**

- [ ] Validation rejects latitude outside [-90, 90]
- [ ] Validation rejects longitude outside [-180, 180]
- [ ] Clear error messages indicating valid ranges
- [ ] Tests verify boundary conditions (exactly -90, 90, -180, 180 are valid)
- [ ] Existing valid coordinates remain unaffected

**Edge Cases**

- Exactly 0,0 (Gulf of Guinea) is valid but unusual - don't reject
- NULL coordinates should be allowed if location is optional
- Consider floating-point precision (89.9999999 vs 90.0000001)

**Helpful References**

- See how date validation works in `DateColumnProcessor`
- WGS84 coordinate system: `docs/geolocation/coordinate-systems.md`
- Similar validation pattern: `app/Validators/NumericRangeValidator.php`

---

## Example 2: New Feature

### Add distance-based filtering for user queries

**Problem**

Users cannot filter by proximity ("find all users within 50km of Copenhagen"), blocking sales teams from finding nearby customers and support teams from routing tickets efficiently.

**Context**

Geolocation data includes pre-computed geohash for efficient proximity queries. The filter should support:

- **Center point:** Specified by coordinates (lat, lng) or address (geocoded)
- **Radius:** Distance in kilometers
- **Result ordering:** Nearest first (optional)
- **Performance:** Must work efficiently with 100k+ user records

Filter modes needed:
1. "Within radius" - users inside circle
2. "Sorted by distance" - all users, ordered by distance

**Acceptance Criteria**

- [ ] Filter UI accepts center point (address input with autocomplete)
- [ ] Filter UI accepts radius in kilometers
- [ ] Backend converts center point to coordinates if address provided
- [ ] Backend uses geohash bounding box for initial filtering
- [ ] Results ordered by actual distance (nearest first)
- [ ] Tests verify distance calculations (Haversine formula)
- [ ] Performance acceptable for 100k+ records (< 500ms query time)

**Edge Cases**

- Handle queries crossing the antimeridian (±180° longitude)
  - e.g., Center at 179°E with 500km radius crosses to -179°W
- Handle polar regions (latitude near ±90°)
  - Geohash precision degrades near poles
- Handle very large radii (> 5000km)
  - Consider using different algorithm for global searches
- Handle NULL coordinates (users without location)
  - Exclude from results or show separately?

**Helpful References**

- Geohash proximity algorithm: `docs/geolocation/geohash-proximity.md`
- Haversine distance formula: `docs/geolocation/distance-calculations.md`
- Similar pattern: Date range filters in `DateFilterApplicator`
- Query optimization: `docs/geolocation/query-optimization.md`

**Dependencies**

Depends on RAS-456 (geohash index creation) because we need the database index for performant proximity queries with large datasets.

---

## Example 3: Testing

### Add integration tests for geocoding service

**Problem**

The geocoding service has no integration tests, making it risky to modify and difficult to debug issues with third-party API integration. Recent production bug (incorrect coordinates for certain addresses) would have been caught with proper tests.

**Context**

The geocoding service converts addresses to coordinates using Google Maps API. Tests should verify:

- Successful geocoding (address → coordinates)
- Invalid address handling (no results from API)
- Ambiguous address handling (multiple results)
- API rate limiting behavior (429 responses)
- Network error handling (timeouts, connection failures)
- Caching behavior (don't re-geocode same address)

**Acceptance Criteria**

- [ ] Test successful address → coordinates conversion
- [ ] Test invalid/nonexistent address returns error
- [ ] Test ambiguous address (multiple results) returns first match
- [ ] Test API rate limit response triggers retry logic
- [ ] Test network timeout triggers fallback behavior
- [ ] Test caching prevents duplicate API calls for same address
- [ ] Tests use mocked API responses (no real API calls during tests)
- [ ] Tests cover both sync and async geocoding paths

**Edge Cases**

- Empty string address
- Address with special characters (é, ñ, 中文)
- Very long addresses (> 200 characters)
- Addresses in unsupported countries
- API returns coordinates but with low confidence score

**Helpful References**

- HTTP mocking patterns: See `tests/Feature/ExternalApi/` for examples
- Geocoding service implementation: `app/Services/GeocodingService.php`
- Google Maps API documentation: `docs/integrations/google-maps-api.md`
- Testing conventions: `.claude/rules/backend/testing-conventions.md`

---

## Example 4: Refactoring

### Extract coordinate validation into reusable validator

**Problem**

Coordinate validation logic is duplicated in 3 places (UserService, LocationService, MapController), making it hard to maintain and causing inconsistent validation behavior across the system.

**Current duplication:**
```php
// UserService.php:145
if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    throw new ValidationException('Invalid coordinates');
}

// LocationService.php:89
if (!($lat >= -90 && $lat <= 90) || !($lng >= -180 && $lng <= 180)) {
    return false; // Different error handling!
}

// MapController.php:234
// ... similar validation with different error messages
```

**Context**

All three services validate lat/lng ranges identically, but with:
- Different error handling (exceptions vs booleans vs null)
- Different error messages
- Slightly different validation logic (< vs <=)

This should be extracted into a dedicated `CoordinateValidator` class that all services use.

**Acceptance Criteria**

- [ ] `CoordinateValidator` class created with validation logic
- [ ] UserService uses the validator (remove duplication)
- [ ] LocationService uses the validator (remove duplication)
- [ ] MapController uses the validator (remove duplication)
- [ ] Validation behavior unchanged (no regressions)
- [ ] Error messages consistent across all uses
- [ ] Tests verify validator works in all three contexts
- [ ] Tests verify edge cases (boundary values)

**Edge Cases**

- Maintain backward compatibility with existing error handling
- Each service may need different error handling (exception vs boolean)
- Consider making validator flexible enough for both cases

**Helpful References**

- Validator pattern: See `app/Validators/EmailValidator.php`
- Related validation: `app/Validators/DateRangeValidator.php`
- Service refactoring pattern: `app/Services/Shared/`

---

## Example 5: Documentation

### Document geolocation privacy zones feature

**Problem**

The privacy zones feature (fuzzing coordinates for privacy-sensitive users) is implemented but not documented, causing confusion for developers and support teams.

**Context**

Privacy zones allow users to hide their exact location by fuzzing coordinates to ~1km radius. This feature:
- Activated via user settings
- Adds random offset to coordinates (±0.01 degrees)
- Stores both exact and fuzzed coordinates
- Uses fuzzed version for public displays
- Uses exact version for internal calculations (territory assignment)

Need documentation for:
- How to enable privacy zones
- How fuzzing algorithm works
- When to use exact vs fuzzed coordinates
- Privacy implications and guarantees

**Acceptance Criteria**

- [ ] User guide explains how to enable privacy zones
- [ ] Technical docs explain fuzzing algorithm
- [ ] API documentation shows which endpoints return fuzzed coordinates
- [ ] Code examples show how to access exact coordinates (for internal use)
- [ ] Privacy policy implications documented
- [ ] Support team runbook for handling privacy zone issues

**Helpful References**

- Existing privacy documentation: `docs/privacy/`
- Similar feature documentation: `docs/features/anonymous-mode.md`
- API documentation standards: `docs/api/documentation-standards.md`

---

## Example 6: Performance Optimization

### Optimize geohash index for proximity queries

**Problem**

Proximity queries ("users within 50km") are slow with large datasets (> 100k users), taking 5-10 seconds for complex queries. This blocks sales teams from using the feature effectively.

**Context**

Current implementation uses geohash bounding box queries, but:
- Index on `location->>'geohash'` is not optimal for range queries
- Multiple geohash prefix queries combined with OR (slow)
- No query result caching

Performance targets:
- < 500ms for radius queries with 100k users
- < 1s for radius queries with 1M users

**Acceptance Criteria**

- [ ] Proximity queries under 500ms for 100k users
- [ ] Proximity queries under 1s for 1M users
- [ ] Index optimizations don't impact write performance significantly
- [ ] Tests verify query performance meets targets
- [ ] Slow query logs show improvement

**Edge Cases**

- Very large radii (> 1000km) may need different approach
- Queries near antimeridian need multiple geohash prefixes
- Polar regions have different optimization needs

**Helpful References**

- Current implementation: `app/Services/DataObjectQuery/FilterApplicators/ProximityFilterApplicator.php`
- Geohash optimization guide: `docs/geolocation/query-optimization.md`
- Database indexing: `.claude/rules/backend/database-conventions.md`

**Dependencies**

Should be completed before RAS-567 (mobile app geolocation features) which will increase query volume significantly.
