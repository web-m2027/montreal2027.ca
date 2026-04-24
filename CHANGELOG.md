# Montreal 2027 WorldCon Website

## Changelog
---
## 1.0.4 (2026-04-24)

- Docker setup
- Added taxonomy vocabulary for staff listing
- Created view and view template to show staff listing
- Added modal popup to send email to staff member

---

## 1.0.3 (2026-04-22)

- Updated guest node template to use explicit fields to better control markup
- Refactored grid layout for guest article content
- Refactored guest article SCSS and adjusted image properties

---

## 1.0.2 (2026-03-12)

- Updated issue templates

---

## 1.0.1 (2026-03-11)

- Applied style improvements across the site
- Fixed spacing of buttons on search results page
- Updated deploy script
- Configured search using SearchAPI
- Styled new search bar in footer
- Styled search results page

---

## 1.0.0 (2026-01-21)

**Major Features:**
- Upgraded to Drupal 11.2.10
- Installed ECA and related modules to enable automatic updates of membership rates
- Added custom module for ability to set a link to open in a new tab

**Styling & Design:**
- Converted rgba colors to oklch where applicable
- Updated font sizes, line heights with clamp() for better responsiveness
- Added site-wide color for links
- Button styling improvements
- Typography changes and adjustments
- Margin, padding and typography adjustments
- Moved social media icons into navigation
- Removed breadcrumb display
- Added hover states for interactive elements
- Adjusted styling for basic pages

**Content & Images:**
- Added and updated image styles
- Added hero image field to home page content type
- Renamed lefleur block template to a more generic theme-image template
- Added content blocks for the canadasaurus images
- Removed 1:1 image constraint from guest images on default page

**Hotels Page:**
- CSS for hotels page implementation
- Updated deploy configuration
- New user roles

**Deployment & Infrastructure:**
- Updated workflow to deploy to dev when a feature branch is pushed
- Updated deploy configuration
