
ARG ENVIRONMENT=latest 

FROM paulborielabs/rembg:${ENVIRONMENT} as build
FROM jonasal/nginx-certbot:latest

COPY --from=build /var/www/html/public /usr/share/nginx/html
RUN rm /usr/share/nginx/html/*.php \
    && rm /usr/share/nginx/html/*.html