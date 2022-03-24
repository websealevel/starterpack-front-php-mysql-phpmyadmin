# Noframework please, une série d'articles pour les devs et webmasters

Voici une série d'articles sur un ou plusieurs petits projets où l'on va surtout construire les choses nous mêmes, pour apprendre ou revoir les bases. Dans la vraie vie on utilisera sûrement des frameworks pour booster notre productivité, assurer une bonne maintenance en travail d'équipe, se donner des règles et ne pas réinventer la roue (qui a déjà été mieux faite par quelqu'un d'autre). Mais les frameworks font surtout ce que l'on veut plus faire. Et c'est quand même utile de comprendre _honnêtement_ ce que l'on ne veut plus faire _avant_ de ne plus le faire (parfois plus jamais).

## Le but de ces articles

J'ai envie de remanier les fondamentaux des technologies du web : comment fonctionne un CGI, les requêtes HTTP, faire des bonnes requêtes SQL, comment sécuriser un service web etc... Mais aussi tester le TDD ou des choses du genre. Apprendre un bon langage de script comme Perl par exemple, pour automatiser au maximum les tâches de webmaster. Ce qui m'a motivé à faire ce voyage aux sources c'est des vieux bouquins d'occasion. Des bouquins des années 2000 principalement. Notamment de la très bonne édition O'REILLY. Dans ces livres qui ont pris de la poussière, en évitant les quelques choses tombées en déséitude, on y trouve des choses précieuses et intemporelles, malgré le vacarme incessant de nouvelles technologies ou de stacks.

On le sait pourtant, on fait du neuf avec du vieux. Le fond de l'affaire lui n'a pas beaucoup évolué. Le web est toujours basé sur des requêtes HTTP, la majorité des sites et applications webs (où est la limite entre ces deux choses d'ailleurs ?) fonctionnent sur du CGI. On sert toujours de l'HTML au client. Aussi, alors que 20 ans dans le monde du développement informatique semble être une éternité, les choses importantes demeurent souvent les mêmes. C'est ces choses là que j'ai envie de bidouiller.

Cette série d'articles n'est pas écrite par un expert, si j'entreprends ce petit voyage c'est aussi (et avant tout) pour apprendre, ré-apprendre et partager. Je vais sûrement aborder des choses que je comprends bien, et d'autres moins. Mais c'est pas grave. Le monde technique du web est si vaste, des protocoles bas niveau aux concepts abstraits, qu'il est de toute manière impossible d'en faire le tour en une vie. Profitons en quand même.

J'aborderai donc cette série avec plus ou moins de détails en fonction de ce qui m'intéresse, sans être exhaustif, sans m'attarder avec rigueur sur tous les points rencontrés. J'essaierai cependant du mieux que je peux de fournir des références vers des ouvrages/articles/vidéos/posts où des gens bien plus intelligents et malins que moi vous expliqueront ce qu'il y a à savoir.

## La stack de légende

Pour explorer le développement web de manière "bas niveau", sans frameworks, je vais utiliser une stack de légende, qui fait toujours ses preuves et fait tourner 80% du web mondial, à savoir une bonne vieille stack PHP/Perl/MySQL/HTML/CSS/JS. Rien de plus classique. Complètement _has been_ sur Twitter.

Pourquoi ce choix ? Parce que c'est une stack canonisée, PHP est simple à prendre en main et c'est très didactique. Les bases relationnelles car je pense que c'est plus intéressant que les bases NoSQL en terme de potentiel et de conception. On peut faire du NoSQL (en gros stocker du JSON) dans une base relationnelle comme MySQL. Et j'ai envie de descendre tout près de la base pour voir comment on peut remettre de la logique métier dans le SGDB au lieu d'écrire du code pas optimal du côté serveur web.

Côté client, du bon vieux statique : HTML/CSS/JS sans framework à la mode. On reste les mains dans le cambouis. On comprendra peut être mieux d'ailleurs pourquoi on a inventé ces frameworks (ou pas ?).

Pourquoi Perl ? Car j'en ai fait à la fac et j'en avais de bons souvenirs pour manipuler du texte. J'ai récemment appris qu'on pouvait faire du web avec Perl, qu'on pouvait le faire executer en CGI par un programme serveur. Je suis curieux de voir son potentiel pour faire du web. Et je pense que c'est un très bon couteau-suisse à avoir dans sa poche pour automatiser plein de tâches, extraire de l'information sur notre système sans avoir à faire un peu de bash par ci, un peu de awk par là, un peu de sed par ci. Tout est dans Perl, alors essayons Perl aussi bien en tant que couteau-suisse qu'en lieu et place de PHP. On verra si Perl est si horrible à comprendre/lire/maintenir comme on le lit partout. Moi en tout cas, son côté langage script hacky de marginal m'attire.

Je sais pas vous mais moi, cette stack, elle me fait rêver. J'ai l'impression d'avoir de grands pouvoirs entre mes mains pour pas cher. Sans trop de cablages. Elle sent bon les débuts du web, elle sent bon le coeur du web.

## La programmtion fonctionnelle et le web

Y'a aussi la programmation fonctionnelle que j'ai envie de tester et d'apprendre pour faire des trucs dans le web. Les contreparties de qualité de vie et de modularité que ce paradigme offre fait rêver sur le papier. Pas le secteur du dev qui permettra de bouffer le plus facilement on le reconnaitra. Mais quand même, envie de gouter au Lisp avec Scheme et pour le web Elm me semble être vraiment un outil prometteur (qui ne rêve pas d'avoir leur compilateur ?). On verra si on a le temps. D'ailleurs pas besoin de langages estampillés fonctionnel pour en faire. On essaiera d'en faire parfois en PHP aussi.

Côté base de données, le fonctionnel donne Datomic. Qui m'intrigue de fou.

## Sommaire

- [Article 1 : se fabriquer son starterpack](./article1-starterpack.md)
