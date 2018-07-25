# Image Library

An image library built with ExpressJs, ReactJs, NextJs and Laravel

See [Api repository](https://github.com/Godofbrowser/laravel-image-library-api)

See [App repository](https://github.com/Godofbrowser/react-image-library-app)

## Key technologies
- SPA with react
- Server side rendering with nextjs
- Oauth with laravel passport

## Installation

-   // Todo

## Improvements

### client
1. CSRF protection on the express server
2. Improve frontend validations
3. Add visual indicator on the frontend app so user knows a server load is in progress
4. Authenticate via third party providers (eg facebook)
5. lazy load images
6. Add a nice notification system to render alerts
7. Add a nice confirmation [dialog](https://github.com/Godofbrowser/vuejs-dialog/)
8. Import configs and credentials
9. infinite scroll
9. Props validation (propTypes)

### api
1. Add uuid to models to replace publicly available ids
2. Save multiple image dimensions for faster page load
3. (Efficiency) Find a way to prevent issuing too many redundant tokens
4. Properly take care of attributes that should be hidden
5. Ability to manage uploads
6. Create separate endpoint for upload so user gets to fill in image details while upload and image processing is running in the background, before finally saving.
7. Pagination
8. Import configs and credentials (done)

## Known Bug (fixed - added a helper method to check a list of guards and return the authenticated user if any)

The application allows users to view some content pages (home and images) as a guest and as an authentcated user.
I have added a computed property `is_owner` to images such that the value is true if the current user is signed in and is the owner of the image, and false otherwise.
Now, the problem is whenever a user views these pages  (home and images), the api doesn't seem to recognize them as authenticated and because of this the `is_owner` attribute becomes `false`. On the dashboard page which has the `auth` middleware, it works fine.

## Todo


- Fix known bug (done)
- During upload, show another interface after clicking on upload that allows user add name, tags and set visibility of images (Upload begins in the background to save time while user fills these details)
- Implement rating
- Export configuration variables and credentials (done)
- Update readme with installation process