FROM docker.elastic.co/beats/filebeat:9.2.3

USER root

# Copy configuration and template
COPY --chown=root:filebeat filebeat.yml /usr/share/filebeat/filebeat.yml
COPY --chown=root:filebeat template.json /usr/share/filebeat/template.json

RUN chmod go-w /usr/share/filebeat/filebeat.yml /usr/share/filebeat/template.json

USER filebeat
