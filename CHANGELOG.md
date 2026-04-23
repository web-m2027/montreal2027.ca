# Montreal 2027 WorldCon Website

## Changelog
---
## 1.0.2 (2026-03-12)

### PR #17: Update issue templates ([8c492fb](https://github.com/Montreal-2027/montreal2027.ca/commit/8c492fb))
- Update issue templates

### PR #15: Update issue templates ([2a7292c](https://github.com/Montreal-2027/montreal2027.ca/commit/2a7292c))
- Update issue templates

### PR #13: Update issue templates ([185ae41](https://github.com/Montreal-2027/montreal2027.ca/commit/185ae41))
- Update issue templates

---

## 1.0.1 (2026-03-11)

### PR #11: Style changes ([0147f2a](https://github.com/Montreal-2027/montreal2027.ca/commit/0147f2a))
- Applied style improvements across the site

### PR #10: Search results page improvements ([f35fa3d](https://github.com/Montreal-2027/montreal2027.ca/commit/f35fa3d))
- Fixed spacing of buttons on search results page
- Updated deploy script

### PR #9: Search implementation and styling ([8186939](https://github.com/Montreal-2027/montreal2027.ca/commit/8186939))
- Configured search using SearchAPI
- Styled new search bar in footer
- Styled search results page

---

## 1.0.0 (2026-01-21)

### PR #5: Initial site development ([033fe27](https://github.com/Montreal-2027/montreal2027.ca/commit/033fe27))

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
