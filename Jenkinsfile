pipeline{

    agent any

    environment{
        commitID = ''
        image = ''
        tag = ''
        dockerImage = ''
        PROJECT_NAME = sh(script: 'basename -s .git ${GIT_URL}', returnStdout: true).trim().toUpperCase()
        RELEASE_VERSION = "${env.GIT_COMMIT}"
        APP_SERVICE_NAME = 'php-exception-handler'
    }

    parameters {
        string(name: 'MailerRecipients', defaultValue: 'fetra.harinjatovo@pulse.mg', description: 'Email recipients')
        string(name: 'MailerReplyTo', defaultValue: 'fetra.harinjatovo@pulse.mg', description: 'Reply Mail')
        string(name: 'MailerSender', defaultValue: 'jenkins-mobile@moov.mg', description: 'Sender Mail')
    }

    stages{

        // stage('Start Build'){
        //     steps{
        //         emailext (
        //             from: "${params.MailerSender}",
        //             to: "${params.MailerReplyTo}",
        //             subject: "STARTED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'",
        //             body: """<p>STARTED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]':</p>
        //             <p>Check console output at &QUOT;<a href='${env.BUILD_URL}'>${env.JOB_NAME} [${env.BUILD_NUMBER}]</a>&QUOT;</p>""",
        //             recipientProviders: [[$class: 'DevelopersRecipientProvider']]
        //         )
        //     }
        // }

        // ###################### BEGIN OWASP DEPENDENCY CHECK #########################
        // stage('Dependency Check') {
        //     steps {
        //         script {
        //             dependencyCheck (
        //                 odcInstallation: "DP-check",
        //                 additionalArguments: "--format XML --scan ${WORKSPACE}"
        //             )
        //         }
        //     }
        // }

        // stage('Dependency Check Publisher') {
        //     steps {
        //         script {
        //             step([$class: 'DependencyCheckPublisher', pattern: "**/dependency-check-report.xml", totalThresholdAnalysisExploitable: true, newThresholdAnalysisExploitable: true])
        //         }
        //     }
        // }
        // ###################### END OWASP DEPENDENCY CHECK #########################

        stage("Test"){
            steps {
                sh "docker-compose -f compose.yaml up -d --build --force-recreate --remove-orphans"
                sh "docker-compose -f compose.yaml ps"
                sh "docker exec -i ${env.APP_SERVICE_NAME} /var/www/html/vendor/bin/phpunit --log-junit build/test-results/junit.xml || true"
            }

            post {
                always {
                    sh "docker cp ${env.APP_SERVICE_NAME}:/var/www/html/build build"
                    junit(testResults: 'build/test-results/**/*.xml', allowEmptyResults: true)
                    archiveArtifacts artifacts: 'build/test-results/**/*.xml', fingerprint: true
                    sh "docker-compose -f compose.yaml down --remove-orphans -v"

                    script {
                        echo "------------- Stage ${env.STAGE_NAME} has been marked as ${currentBuild.result}  ------------"
                    }

                }
                unstable {
                    script {
                        echo "Some tests failed in the previous stage, but the pipeline continues to run"
                    }
                }
            }
        }


        stage('Review Sonar') {
            steps {
                withSonarQubeEnv([installationName: 'sonar-devops', envOnly: true]) {
                    script {
                        def branchName = "${GIT_BRANCH.split('/')[-1].toUpperCase()}"
                        def scannerHome = tool 'Sonar_Scanner'
                        sh "${scannerHome}/bin/sonar-scanner -X -Dsonar.mode=incremental -Dsonar.qualitygate.wait=true -Dsonar.projectKey=${APP_SERVICE_NAME} -Dsonar.projectVersion=${RELEASE_VERSION} -Dsonar.sources=. || true"
                    }
                    
                }
            }
        }


    }
    post{
        always{
            //deleteDir()
            cleanWs()
        }
    }

} 
