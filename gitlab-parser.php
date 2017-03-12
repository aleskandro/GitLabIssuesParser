<?php
/* This file is part of GitlabIssuesParser
 *
 * GitlabIssuesParser is free software: you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 *
 * GitlabIssuesParser is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with 
 * GitlabIssuesParser. 
 * If not, see http://www.gnu.org/licenses/.
 */

/**
* * GitlabIssuesParser
* * A basic parser for the GitLab issues atom feed
* * This is a basic php script to get a transformation on the atom feed from a 
* * GitLab account (also in a self-hosted GitLab installation) from the atom 
* * feed to other forms more readable by simple readers.
* * I had to create it to get datas from Gitlab in Software that doesn't support
* * a good implementation for atom feeds
* *
* * LICENSE: GNU/GPLv3
* *
* * @category   Parser
* * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
* * @version    0.1
* *
* */

// Simple authentication for this script (unsecure)
//
if ($_GET['token'] != 'ANYRANDOMTOKENTOPASSTOTHEGETREQUEST')
        exit;

/**
 * Get the query from the $_GET array associated with the request
 *
 * @return string The query encoded for the request to forward to the Gitlab host
 */

function _get_query()
{
        unset($_GET['token']);
        $ret = implode('&', array_map(
                function ($v, $k) { return sprintf("%s=%s", rawurlencode($k), rawurlencode($v)); },
                $_GET,
                array_keys($_GET)
        ));
        return $ret;
}

/**
 * Create the url based on the $base_url argument and the elements of the GET request done
 *
 * @param string $base_url the base address of the Gitlab host 
 *
 * @return string The complete URL to get the requested issues
 */
function _get_url($base_url)
{
        $resource = "";
        if (null !== (_GET['group']))
                $resource = "/groups/${_GET['group']}/";
        elseif(null !== $_GET['project'] && null !== $_GET['user'])
                $resource = "/${_GET['user']}/${_GET['project']}";
        else
        {
                echo "Query error; set user/project or group";
                exit;
        }
        unset($_GET['group']);
        unset($_GET['project']);
        unset($_GET['user']);

        $query = _get_query();

        return $base_url . $resource . 'issues.atom?' . $query;
}

/**
 * Get all the issues from the the Gitlab issues atom feed required
 *
 * @param string $base_url The base url to call the _get_url(..) function
 *
 * @return array an array of SimpleXMLObject containing all the issues returned by the GET request
 */
function get_atom_as_xml($base_url)
{
        $url = _get_url($base_url);
        $xml = array();
        $pagenumber = 1;
        while(true) 
        {
                $t = simplexml_load_file($url."&page=$pagenumber");
                $pagenumber++;
                if (!$t -> entry) break;
                array_push($xml, $t);
        }
        return $xml;
}

/**
 * Get an array of entries from the Gitlab's issues atom feed
 *
 * @param array $xml an array of SimpleXMLObject that, for example, could  represent all the pages
 *                   that contain the issues for the resource required
 *
 * @return array An array of the issues represented as an array
 */
function _to_array($xml)
{
        $issues = Array();
        foreach($xml as $xmlpage)
        {
                $entries = $xmlpage -> entry;
                foreach ($entries as $entry)
                {
                        $array = Array();
                        $project = preg_replace("/.*\/([^\/]*)\/issues\/.*/","\$1", $entry -> id);
                        $id = preg_replace("/.*\/([^\/]*)\/issues\/(.*)/","\$2", $entry -> id);

                        array_push($array, $project); //Project's Name
                        array_push($array, $id); //Issue's id
                        array_push($array, (string)$entry -> id); // Issue's url
                        array_push($array, (string)$entry -> assignee -> name);
                        array_push($array, (string)$entry -> due_date);
                        //array_push($array, $entry -> labels);
                        array_push($array, (string)$entry -> title);
                        array_push($array, (string)$entry -> summary);
                        array_push($array, (string)$entry -> description);
                        array_push($array, (string)$entry -> author -> name);
                        array_push($array, (string)$entry -> updated);
                        array_push($issues, $array);
                }
        }
        return $issues;
}

/**
 * Get an associative array of the entries from the Gitlab's issues atom feed
 *
 * @param array $xml an array of SimpleXMLObject that, for example, could  represent all the pages
 *                   that contain the issues for the resource required
 *
 * @return array An array of the issues represented as an associative array
 */
function _to_associative_array($xml)
{
        $ass_array = Array();
        $issues = _to_array($xml);

        foreach($issues as $issue)
        {
                $single_object = [ 
                        'Project' => $issue[0],
                        'IssueId' => $issue[1],
                        'Url' => $issue[2],
                        'Assignee' => $issue[3],
                        'DueDate' => $issue[4],
                        'Title' => $issue[5],
                        'Summary' => $issue[6],
                        'Description' => $issue[7],
                        'Author' => $issue[8],
                        'Updated' => $issue[9]
                ];
                array_push($ass_array, $single_object);
        }
        return $ass_array;
}

/**
 * Generate a simple csv with custom separator and row_separator
 *
 * @param array $xml An array of SimpleXMLObject that, for example, could  represent all the pages
 *                   that contain the issues for the resource required
 * @param string $separator Item separator (Default: "|#|")
 * @param string $row_separator Issues separator (Default: "|$$$|")
 *
 * @return string The output csv containing the issues 
 */
function get_csv($xml, $separator  = "|#|" , $row_separator = "|$$$|")
{
        $header = ['Project', 'Issue', 'Url', 'Due date', 'Title', 'Summary', 'Description', 'Author\'s name', 'updated'];
        $csv = join($separator, $header) . $row_separator;
        $issues = _to_associative_array($xml);
        foreach ($issues as $issue)
                $csv .= (join($separator, $issue) . $row_separator);

        return $csv;
}

/**
 * Create a json string from the associative array representation of the issues in the XML given
 *
 * @param array $xml An array of SimpleXMLObject that, for example, could  represent all the pages
 *                   that contain the issues for the resource required
 *
 * @return string The output JSON string containing the issues 
 */
function get_json($xml)
{
        return json_encode(_to_associative_array($xml));
}

/**
 * Create a simple XML from the associative array representation of the issues in the XML given
 *
 * @param array $xml An array of SimpleXMLObject that, for example, could  represent all the pages
 *                   that contain the issues for the resource required
 *
 * @return SimpleXMLObject The output XML object containing the issues 
 */
function get_clear_xml($xml)
{
        //      print($xml->asXML());
        //
        $clear_xml = new SimpleXMLElement('<root/>');
        $array = _to_associative_array($xml);
        foreach ($array as $item)
        {
                $xml_item = $clear_xml -> addChild('item');
                foreach ($item as $key => $value)
                {
                        $xml_item -> addChild($key, $value);
                }
        }
        return $clear_xml;
}

/**
 * Print the XML clear from get_clear_xml
 *
 * @param string $xml the $xml from get_atom_as_xml($base_url)
 * 
 * @return void 
 */
function print_clear_xml($base_url)
{
        $xml = get_atom_as_xml($base_url);
        $cxml = get_clear_xml($xml);
        Header('Content-type: text/xml');
        echo $cxml -> asXML();
}


/**
 * Examples functions
 *
 * @return void
 */
function examples()
{
        $base_url = "https://mygitlab.mydomain.mytld";
        $xml = get_atom_as_xml($base_url);

        // Print the xml cleared (it sets the header content-type so is commented)
        //print_clear_xml($xml); 

        // Print the JSON string
        echo get_json($xml); 

        // Print the csv with default parameters
        echo get_csv($xml);

}

examples();
?>

