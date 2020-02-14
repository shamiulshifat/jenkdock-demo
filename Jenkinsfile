pipeline {
  agent {
    docker {
      image 'allebb/phptestrunner-74:latest'
      args '-v $HOME:/var/www/html'
    }

  }
  stages {
    stage('Build') {
      agent any
      steps {
        sleep 10
        echo 'Building test environment'
        sh '#php -v'
        echo 'Installing Composer'
        sh 'curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer'
        echo 'Installing PHPLoc...'
        sh 'composer global require phploc/phploc --no-progress'
        echo 'Installing PHPMD...'
        sh 'composer global require phpmd/phpmd --no-progress'
        echo 'Installing project composer dependencies...'
        dir(path: '/var/www/html ') {
          sh 'composer install --no-progress'
        }

      }
    }

    stage('Test') {
      steps {
        echo 'Running PHPUnit tests...'
        sh 'php /var/www/html/vendor/bin/phpunit'
        echo 'Running PHPLoc...'
        sh 'php /root/.composer/vendor/bin/phploc lib'
        echo 'Running PHPMD...'
        sh 'php /root/.composer/vendor/bin/phpmd lib text'
      }
    }

    stage('Deploy') {
      steps {
        echo 'Pipeline completed!'
      }
    }

  }
}