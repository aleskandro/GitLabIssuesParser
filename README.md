
===============

## GitlabIssuesParser
A basic parser for the GitLab issues atom feed

This is a basic php script to get a transformation on the atom feed from a
GitLab account (also in a self-hosted GitLab installation) from the atom
feed to other forms more readable by simple readers.
I had to create it to get datas from Gitlab in Software that doesn't support
a good implementation for atom feeds

LICENSE: GNU/GPLv3


Functions
-------


### _get_query

    string _get_query()

Get the query from the $_GET array associated with the request







### _get_url

    string _get_url(string $base_url)

Create the url based on the $base_url argument and the elements of the GET request done






#### Arguments
* $base_url **string** - the base address of the Gitlab host



### get_atom_as_xml

    array get_atom_as_xml(string $base_url)

Get all the issues from the the Gitlab issues atom feed required






#### Arguments
* $base_url **string** - The base url to call the _get_url(..) function



### _to_array

    array _to_array(array $xml)

Get an array of entries from the Gitlab's issues atom feed






#### Arguments
* $xml **array** - an array of SimpleXMLObject that, for example, could  represent all the pages
                  that contain the issues for the resource required



### _to_associative_array

    array _to_associative_array(array $xml)

Get an associative array of the entries from the Gitlab's issues atom feed






#### Arguments
* $xml **array** - an array of SimpleXMLObject that, for example, could  represent all the pages
                  that contain the issues for the resource required



### get_csv

    string get_csv(array $xml, string $separator, string $row_separator)

Generate a simple csv with custom separator and row_separator






#### Arguments
* $xml **array** - An array of SimpleXMLObject that, for example, could  represent all the pages
                  that contain the issues for the resource required
* $separator **string** - Item separator (Default: &quot;|#|&quot;)
* $row_separator **string** - Issues separator (Default: &quot;|$$$|&quot;)



### get_json

    string get_json(array $xml)

Create a json string from the associative array representation of the issues in the XML given






#### Arguments
* $xml **array** - An array of SimpleXMLObject that, for example, could  represent all the pages
                  that contain the issues for the resource required



### get_clear_xml

    \SimpleXMLObject get_clear_xml(array $xml)

Create a simple XML from the associative array representation of the issues in the XML given






#### Arguments
* $xml **array** - An array of SimpleXMLObject that, for example, could  represent all the pages
                  that contain the issues for the resource required



### print_clear_xml

    void print_clear_xml($base_url)

Print the XML clear from get_clear_xml






#### Arguments
* $base_url **mixed**



### examples

    void examples()

Examples functions







