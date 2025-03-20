# Sonde nagios / shinken pour FACILE

Mail du CINES ranier@cines.fr) du 16/12/2016 :

    Bonjour,
    
    Avec un peu de retard, voici la sonde que nous utilisons pour
    tester si Facile est en forme.
    Je vous ai transmis également un petit fichier PDF qui est censé
    être validé rapidement, à utiliser avec.
    
    Les dépendances à installer sont en entêtes du fichier shell.
    
    Il s'utilise comme suit:
    ./check_webservice_response.sh -w 1000 -c 5000 -u https://facile.cines.fr/xml sample.pdf
    
    -w 1000 signifie que la sonde émet un warning si le service met plus de 1000ms
    à répondre
    -c 5000 la sonde émet un critical si le service met plus de 5000 ms à répondre.
    
    exemple de réponses:
    RESPONSE: OK - 893 ms|Response=893ms;1000;5000;0
    
    RESPONSE: CRITICAL - 5493 ms|Response=5493ms;1000;5000;0
    
    Bonnes fêtes,
    Alexandre Granier
    
    
    Alexandre GRANIER
    CINES, Centre Informatique National de l’Enseignement Supérieur
    950, rue de Saint Priest
    34097 MONTPELLIER cedex 5

