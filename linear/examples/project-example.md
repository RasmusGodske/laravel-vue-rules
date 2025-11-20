# Generic Project Description Example

This is a complete example of a well-written Linear project description.

---

# Geolocation Support for User Profiles

## Overview

Introduces a geolocation data type for user profiles, enabling location-based features while maintaining privacy controls and efficient proximity searching. This allows users to store location data, search by distance, display users on maps, and route to nearby representatives.

## Problem & Context

**Previous challenge:**

Users stored addresses as simple text strings ("123 Main St, Springfield, USA"). This made it impossible to:
- Find users within a specific radius
- Sort results by distance from a point
- Display user locations on interactive maps
- Validate location accuracy
- Perform efficient geographical queries

**User impact:**

- **Sales teams** couldn't find nearby customers for in-person meetings
- **Support teams** couldn't route tickets to local representatives
- **Analytics teams** couldn't identify regional trends or coverage gaps
- **Operations teams** couldn't plan territory assignments efficiently

**Business context:**

With 10,000+ users across multiple countries, manual location management was causing:
- Missed sales opportunities (couldn't identify nearby prospects)
- Slower support response times (routing to wrong regions)
- Inefficient territory planning (no visualization of user distribution)

## Architecture & How It Works

**Data structure:**

```json
{
  "address": "123 Main St, Springfield, IL",
  "lat": 39.7817,
  "lng": -89.6501,
  "_computed": {
    "geohash": "dp3wm7"  // For efficient proximity queries
  }
}
```

**Key decisions:**

- **Store both address and coordinates:** Human-readable address + precise coordinates for flexibility
- **Use geohash for indexing:** Enables fast proximity searches without calculating distances for every row
- **Backend geocoding:** Address-to-coordinate conversion happens server-side (not client-side) for consistency
- **Privacy zones:** Support fuzzing coordinates to ~1km radius for privacy-sensitive users
- **Lazy geocoding:** Only geocode when address changes (not on every save)

**How components interact:**

1. **Input:** User enters address via autocomplete component
2. **Geocoding:** Backend service converts address → lat/lng using Google Maps API
3. **Geohash:** System computes geohash from coordinates for database indexing
4. **Storage:** All three values stored in user profile (address, coordinates, geohash)
5. **Querying:** Proximity filters use geohash bounding box for efficient searches
6. **Display:** Map components render using lat/lng coordinates

## Affected Areas

**Backend:**

- **UserService** - Saving users with location data, validation, geocoding
- **GeocodingService** - New service for address → coordinate conversion via Google Maps API
- **GeolocationValidator** - Validates coordinate bounds, geohash format
- **Query filters** - Distance-based filtering ("within 50km of point"), proximity sorting
- **Cache layer** - Cache geocoding results to reduce API calls

**Frontend:**

- **LocationInput component** - Address autocomplete with map picker
- **MapDisplay component** - Interactive map showing user locations
- **ProximityFilter component** - Distance radius selector with center point picker
- **UserTable component** - Display location data, sort by distance

**Database:**

- **users table** - New `location` JSON column
- **geohash index** - New index on `location->>'geohash'` for proximity queries
- **Migration** - Backfill existing addresses (geocode in background job)

**Integrations:**

- **Google Maps API** - Geocoding and map display
  - Rate limiting: 50 requests/second
  - Caching strategy: Cache for 90 days
  - Fallback: Graceful degradation if API unavailable
- **Background jobs** - Geocode existing addresses without blocking

**Services affected:**

- **TerritoryAssignmentService** - Now can auto-assign based on proximity
- **RoutingService** - Route support tickets to nearest representative
- **AnalyticsService** - New geographical reporting capabilities

## Documentation References

- Geohash algorithm explained: `docs/geolocation/geohash-explainer.md`
- Privacy zones configuration: `docs/geolocation/privacy-zones.md`
- Google Maps API integration: `docs/integrations/google-maps-api.md`
- Query performance optimization: `docs/geolocation/query-optimization.md`

## Known Limitations

- **Geocoding requires internet:** No offline geocoding support (uses Google Maps API)
- **Historical data:** Existing addresses not automatically geocoded (requires background job)
- **Precision limits:** ~10m precision (city-block level), not GPS-precise
- **No computed columns:** Cannot create computed geolocation columns (is_computed not supported)
- **Single location per user:** Users cannot have multiple addresses (home + office)
- **No route planning:** Only distance "as the crow flies", not driving distance/time
- **Address format:** Assumes standard address formats (may not work for rural/remote areas)
