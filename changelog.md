Leave Holidays

TODO LIST
* Mail Leaves pour Paie
* Ajouter calendrier sur working day
* Envoyer mail bouton working day

v1.0 2014/11/27
* Launch in production of the tool
* Compute the number of day for each leave
* Fix a bug for editing leaves
* Remove the validation block if not in state 'to validate'
* Mai for Medical Visit
* Add the Expense system:
    * scan depense@gbandsmith.com and expense@gbandsmith.com for expenses proof
    * put that in a DB + attachments
    * lots of flags to classify the expense

v0.2 2014/10/01
* Holiday balance on list view and new view
* Add Medical Visit information on user admin
* Hierarchical display of users in planning view
* Add flag for TR at user level + Total count per month
* Save GivenTR by month / by user to DB
* Admins : julien ml seb charlotte

v0.01 2014/08/20
* Authentification via GBS LDAP + mass import of users
* Users management (manager)
* Reset password: reset GBS LDAP + GBS OVH mail password at the same time
* Leave Management system:
    * Each employee can ask for leave
    * Each leave must be validated or refused by N+1 or N+2
    * A mail is sent to manager when a leave is asked, and to employee when a leave
     is accepted or refused
    * A planning is available to globally view all leaves (accepted + asked different colors)
