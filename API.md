General
======

API calls to sumochi are made as GET requests for client compatibility reasons. (I know, breaking the web forever, etc.)

They should be made to the URL `http://sumochi.ajf.me/` if you couldn't figure that out.

The URL parameter `p` specifies which API method is to be called.

API methods return a string with two parts, separated by a space. The first is either `SUCCESS` or `ERROR`. The second is result data, or the error name. There will be a new line (LF) at the end of it.

An example of an error:

    ERROR gg2_login_failed
    
An example of a success:

    SUCCESS XXX123

API Methods
===========

`api_login`
-----------

###Example

    http://smoke.ajf.me/?p=api_login&user=joe&password=foobar

(hopefully) resulting in:

    SUCCESS XXX123

###Parameters

* `user` - username of user to log in for
* `password` - password of said user

###Result portion

If it succeeds, the result part of the string will be the login token needed to use some other API methods.

###Possible error values

* `gg2_login_failed` - login failed to GG2F. Something's wrong with Sumochi or the GG2F, or they got their username and password wrong.
* `no_sumochi_user` - this person hasn't logged in for the first time to Sumochi (via the website) yet. They'll need to do this to create their account, essentially.


`api_give_achievement`
----------------------

###Example

    http://smoke.ajf.me/?p=api_give_achievement&user=joe&token=XXX13&key=SERVER_SECRET_KEY&a_id=joesmod_1&a_name=hello,%20world&a_icon=c.png

(hopefully) resulting in:

    SUCCESS

###Parameters

* `user` - username of user to give achievement to
* `token` - login token
* `key` - server's secret key (must be obtained by permission)
* `a_id` - ID value for achievement - if another achievement with same ID exists, giving this achievement will fail with `already_has_achievement`. Recommended to use prefixes to reduce chance of accidental collision, e.g. `ajf_mod_1` not just `1`.
* `a_name` - Name of the achievement. Try to keep it ASCII, the font rendering may barf.
* `a_icon` - (optional) - references by name an icon stored on Sumochi server. Again, you'll have to ask me to add it. Some defaults include `baby.png` (overweight taunt icon), `c.png` (K&R C icon), `eyes.png` (eyes), `sandvich.png` (gg2 sandvich icon) and `test.png` (question mark)

###Possible error values

* `already_has_achievement` - already an achievement with same ID
* `unknown_key` - unrecognised secret key
* `invalid_token` - invalid login token
* `unknown_error` - unknown internal error

GML Scripts
===========

To assist in using the Sumochi API from GML, here is a function to call a sumochi method:

`sumochi_call_api`
------------------

###Usage

    sumochi_call_api(endpoint, method, parameters);

###Parameters

* `endpoint` - URL providing API, usually 'http://sumochi.ajf.me/'
* `method` - the 'p' URL parameter, e.g. 'api_login', 'api_give_achievement'
* `parameter` - the other URL parameters as a ds_map

###Return value

Return value should be a string. If it's empty, something probably went wrong.

###Example use

    arguments = ds_map_create();
    ds_map_add(arguments, 'user', 'foo');
    ds_map_add(arguments, 'password', 'bar');
    
    result = sumochi_call_api('http://sumochi.ajf.me/', 'api_login', arguments);
    
    // ...

###Function source

    endpoint = argument0;
    method = argument1;
    parameters = argument2;
    
    tempfile = 'temp.txt';
    
    // prep URL
    
    url = endpoint;
    
    url += '?';
    
    url += 'p=' + method;
    
    for (key = ds_map_find_first(parameters); is_string(key); key = ds_map_find_next(parameters, key)) {
        val = ds_map_find_value(parameters, key);
        
        // lazily sanitize by replacing all "reserved" characters
        // and space
        // this will probably break for control characters
        // or non-sestets (>127, Latin-1)
        val = string_replace_all(val, ' ', '%20');
        val = string_replace_all(val, '!', '%21');
        val = string_replace_all(val, '#', '%23');
        val = string_replace_all(val, '$', '%24');
        val = string_replace_all(val, '&', '%26');
        val = string_replace_all(val, "'", '%27');
        val = string_replace_all(val, '(', '%28');
        val = string_replace_all(val, ')', '%29');
        val = string_replace_all(val, '*', '%2A');
        val = string_replace_all(val, '+', '%2B');
        val = string_replace_all(val, ',', '%2C');
        val = string_replace_all(val, '/', '%2F');
        val = string_replace_all(val, ':', '%3A');
        val = string_replace_all(val, ';', '%3B');
        val = string_replace_all(val, '=', '%3D');
        val = string_replace_all(val, '?', '%3F');
        val = string_replace_all(val, '@', '%40');
        val = string_replace_all(val, '[', '%5B');
        val = string_replace_all(val, ']', '%5D');
        
        url += '&' + key + '=' + val;
    }

    handle = DM_CreateDownload(url, tempfile);

    DM_StartDownload(handle);

    // 0: invalid handle, 1: ready, 2: downloading, 3: downloaded
    while (DM_DownloadStatus(handle) != 3) {
    }

    DM_StopDownload(handle);
    DM_CloseDownload(handle);

    if(file_exists(tempfile)) {
        handle = file_text_open_read(tempfile);
        text = '';
        while (!file_text_eof(handle)) {
            text += file_text_read_string(handle);
        }
        file_text_close(handle);
        file_delete(tempfile);
        
        return text;
    }
    
    return '';
