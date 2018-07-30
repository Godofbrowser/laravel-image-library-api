# Image Library

An image library built with ExpressJs, ReactJs, NextJs and Laravel

See [Api repository](https://github.com/Godofbrowser/laravel-image-library-api) - [live demo](http://image-library-api.herokuapp.com/)

See [App repository](https://github.com/Godofbrowser/react-image-library-app) - [live demo](http://image-library-app.herokuapp.com/)

## Key technologies
- PWA with workbox
- SPA with react
- SSR with nextjs
- Oauth with laravel passport

## Installation

-   // Todo

## Improvements

### client
1. CSRF protection on the express server
2. Improve frontend validations
3. Add visual indicator on the frontend app so user knows a server load is in progress
4. Authenticate via third party providers (eg facebook)
5. lazy load images (done)
6. Add a nice notification system to render alerts (done)
7. Add a nice confirmation [dialog](https://github.com/Godofbrowser/vuejs-dialog/)
8. Import configs and credentials
9. infinite scroll
10. Ask guests to login or register when they try to rate an image

### api
1. Add uuid to models to replace publicly available ids
2. Save multiple image dimensions for faster page load
3. (Efficiency) Find a way to prevent issuing too many redundant tokens
4. Properly take care of attributes that should be hidden
5. Ability to manage uploads
6. Create separate endpoint for upload so user gets to fill in image details while upload and image processing is running in the background, before finally saving.
7. Pagination

### Ideas
- Show images on a popup or single page when clicked with more details
- Add recently viewed images section to the user dashboard
- Add related images section to the single-image page
- Add other images by user to the single-image page
- Add a download button single-image page


## Todo

- Update readme with installation process
- Write tests
