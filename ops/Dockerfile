FROM phusion/passenger-ruby22:0.9.15
MAINTAINER David Yip <yipdw@member.fsf.org>
CMD ["/sbin/my_init"]

RUN apt-get update

# Dependencies.
RUN apt-get install -y git mysql-server libmysqlclient-dev

# Clone the code.
RUN sudo -iu app sh -c 'cd /home/app && git clone https://github.com/yipdw/shieldsup'
RUN sudo -iu app sh -c 'cd /home/app/shieldsup && git checkout topic/dockerfile-alxcenz'

# Install dependencies.
RUN sudo -iu app sh -c 'cd /home/app/shieldsup && bundle install --path=/home/app/bundle'

# Allow SSH access to the container.
RUN rm -f /etc/service/sshd/down
RUN /etc/my_init.d/00_regen_ssh_host_keys.sh

# Start MySQL at startup.
COPY 10_start-mysql.sh /etc/my_init.d/10_start-mysql.sh

# Do further MySQL configuration (schema creation, root password assignment,
# anything else that needs the server to be up) at startup.
#
# This script looks for the file /tmp/CONFIGURE_MYSQL.  If it is not present,
# it doesn't do anything.
COPY 30_mysql-config.sh /etc/my_init.d/30_mysql-config.sh
RUN umask 0077 && echo -n changeme > /tmp/MYSQL_ROOT_PASSWORD
RUN umask 0077 && echo -n shieldsup > /tmp/SHIELDSUP_DATABASE_NAME
RUN umask 0077 && echo -n shieldsup > /tmp/SHIELDSUP_DATABASE_USERNAME
RUN umask 0077 && echo -n shieldsup > /tmp/SHIELDSUP_DATABASE_PASSWORD
RUN touch /tmp/CONFIGURE_MYSQL

# Copy in the configuration file for said daemon.
COPY conf.yaml /home/app/shieldsup/conf.yaml
RUN chown app:app /home/app/shieldsup/conf.yaml

# Mount Shields Up at /.
RUN rm /etc/nginx/sites-enabled/default
COPY 001_shields-up.conf /etc/nginx/sites-enabled/001_shields-up.conf
RUN rm -f /etc/service/nginx/down

# Make HTTP accessible from outside the Docker environment.
EXPOSE 80

# Uncomment these to add an SSH key for logging in as the app user and root.
# Alternatively, create a new Dockerfile that
# 1. uses the image from this Dockerfile as a base image, and
# 2. contains the lines below.
ADD your_key.pub /tmp/your_key.pub
RUN cat /tmp/your_key.pub >> /root/.ssh/authorized_keys
RUN cat /tmp/your_key.pub >> /home/app/.ssh/authorized_keys
RUN chown -R app:app /home/app/.ssh
RUN rm /tmp/your_key.pub
