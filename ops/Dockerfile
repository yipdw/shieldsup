# This uses phusion/baseimage (a stripped-down Ubuntu) to build a LAMP stack
# for developing Shields Up.

FROM phusion/baseimage:0.9.16
MAINTAINER David Yip <yipdw@member.fsf.org>
CMD ["/sbin/my_init"]

# We use Brightbox's Ruby PPA because they seem trustworthy enough for a
# development VM.  Also, the official Ubuntu packages tend to lag behind.
RUN add-apt-repository ppa:brightbox/ruby-ng

RUN apt-get update

# Dependencies.
RUN apt-get install -y apache2 git mysql-server libmysqlclient-dev php5 ruby2.2-dev ruby-bundler

# Development user.
RUN adduser --disabled-password dev --gecos 'Development user,,,'

# Clone the code.
RUN sudo -iu dev sh -c 'cd /home/dev && git clone https://github.com/yipdw/shieldsup'

# Allow SSH access to the container.
RUN rm -f /etc/service/sshd/down
RUN /etc/my_init.d/00_regen_ssh_host_keys.sh

# Start Apache and MySQL at startup.
COPY 10_start-mysql.sh /etc/my_init.d/10_start-mysql.sh
COPY 20_start-apache.sh /etc/my_init.d/20_start-apache.sh

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

# Put the Twitter-reading daemon under runit, and have it also start at
# startup.
RUN mkdir /etc/service/backend
COPY run-backend /etc/service/backend/run

# Install dependencies for the Twitter daemon.
RUN cd /home/dev/shieldsup/bin && bundle install

# Copy in the configuration file for said daemon.
COPY conf.yaml /home/dev/shieldsup/conf.yaml
RUN chown dev:dev /home/dev/shieldsup/conf.yaml

# Make a place for the daemon's control socket.
RUN mkdir /home/dev/shieldsup/sockets
RUN chown dev:www-data /home/dev/shieldsup/sockets
RUN chmod 775 /home/dev/shieldsup/sockets

# Ditto for the frontend.
COPY conf.php /home/dev/shieldsup/web/conf.php
RUN chown dev:dev /home/dev/shieldsup/web/conf.php

# Mount Shields Up at /.
COPY 001_shields-up.conf /etc/apache2/sites-available/001_shields-up.conf
RUN cd /etc/apache2/sites-enabled && cp -s /etc/apache2/sites-available/001_shields-up.conf .

# Make HTTP accessible from outside the Docker environment.
EXPOSE 80

# Uncomment these to add an SSH key for logging in as dev and root.
# Alternatively, create a new Dockerfile that
# 1. uses the image from this Dockerfile as a base image, and
# 2. contains the lines below.
ADD your_key.pub /tmp/your_key.pub
RUN cat /tmp/your_key.pub >> /root/.ssh/authorized_keys
RUN mkdir /home/dev/.ssh
RUN cat /tmp/your_key.pub >> /home/dev/.ssh/authorized_keys
RUN chown -R dev:dev /home/dev/.ssh
RUN rm /tmp/your_key.pub
