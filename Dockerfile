FROM php:7.2-apache

ENV GITERARY_APPLICATION_NAME   giterary
ENV GITERARY_INSTANCE_NAME      Giterary

ENV GITERARY_BASE_DIR           /var/lib/${GITERARY_APPLICATION_NAME}/
ENV APACHE_HTML_DIR             /var/www/html/

ENV GITERARY_REPO_DIR           ${GITERARY_BASE_DIR}/repo/${GITERARY_INSTANCE_NAME}
ENV GITERARY_SESSION_DIR        ${GITERARY_BASE_DIR}/session/${GITERARY_INSTANCE_NAME}
ENV GITERARY_DRAFT_DIR          ${GITERARY_BASE_DIR}/draft/${GITERARY_INSTANCE_NAME}
ENV GITERARY_TEMP_DIR           ${GITERARY_BASE_DIR}/temp/${GITERARY_INSTANCE_NAME}
ENV GITERARY_AUTH_DIR           ${GITERARY_BASE_DIR}/auth/${GITERARY_INSTANCE_NAME}

ENV DOMAIN_NAME                 giterary.yourdomain.com


EXPOSE 80

RUN apt-get update \
    && apt-get install -y libmemcached-dev zlib1g-dev git telnet \
    && apt-get clean -y \
    && pecl install memcached-3.1.3 \
    && apt-get autoremove -y \
    && docker-php-ext-enable memcached \
    && true

COPY src/ ${APACHE_HTML_DIR}

RUN mkdir -p "${GITERARY_BASE_DIR}" \
    && mkdir -p "${GITERARY_REPO_DIR}" \
    && mkdir -p "${GITERARY_SESSION_DIR}" \
    && mkdir -p "${GITERARY_DRAFT_DIR}" \
    && mkdir -p "${GITERARY_TEMP_DIR}" \
    && mkdir -p "${GITERARY_AUTH_DIR}" \
    && true

RUN sed -i -e "s/'APPLICATION_NAME', 'giterary'/'APPLICATION_NAME', '${GITERARY_APPLICATION_NAME}'/" ${APACHE_HTML_DIR}/include/config.php \
    && sed -i -e "s/'INSTANCE_NAME', 'GITERARY'/'INSTANCE_NAME', '${GITERARY_INSTANCE_NAME}'/" ${APACHE_HTML_DIR}/include/config.php \
    && sed -i -e "s/'COOKIE_DOMAIN', 'giterary.yourdomain.com'/'COOKIE_DOMAIN', '${DOMAIN_NAME}'/" ${APACHE_HTML_DIR}/include/config/base.php \
    && true

RUN touch "${GITERARY_AUTH_DIR}/passfile.csv" \
    && echo "# Username,user.name,user.email,Password Hash" >> "${GITERARY_AUTH_DIR}/passfile.csv" \
    && echo '"anonymous","name","email","sha512$HASH"' >> "${GITERARY_AUTH_DIR}/passfile.csv" \
    && true

RUN git init "${GITERARY_REPO_DIR}" \
    && echo "Welcome to **Giterary**!" > "${GITERARY_REPO_DIR}/Home" \
    && git --git-dir "${GITERARY_REPO_DIR}/.git" --work-tree="${GITERARY_REPO_DIR}" config user.name "Giterary Anonymous" \
    && git --git-dir "${GITERARY_REPO_DIR}/.git" --work-tree="${GITERARY_REPO_DIR}" config user.email "anonymous@giterary.com" \
    && git --git-dir "${GITERARY_REPO_DIR}/.git" --work-tree="${GITERARY_REPO_DIR}" add "${GITERARY_REPO_DIR}/Home" \
    && git --git-dir "${GITERARY_REPO_DIR}/.git" --work-tree="${GITERARY_REPO_DIR}" commit --author "Giterary Anonymous <anonymous@giterary.com>" --message "Giterary repository initialization" \
    && true

RUN chown -R www-data:root "${GITERARY_REPO_DIR}" \
    && chown -R www-data:root "${GITERARY_TEMP_DIR}" \
    && chown -R www-data:root "${GITERARY_SESSION_DIR}" \
    && chown -R www-data:root "${GITERARY_DRAFT_DIR}" \
    && chmod -R u+rw "${GITERARY_DRAFT_DIR}" \
    && chmod -R u+rw "${GITERARY_TEMP_DIR}" \
    && chmod -R u+rw "${GITERARY_SESSION_DIR}" \
    && chmod -R u+rw "${GITERARY_REPO_DIR}" \
    && true

VOLUME ["${GITERARY_BASE_DIR}"]
