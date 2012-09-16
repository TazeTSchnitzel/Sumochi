General
======

API calls to sumochi are made as GET requests for client compatibility reasons. (I know, breaking the web forever, etc.)

They should be made to the URL `http://sumochi.ajf.me/` if you couldn't figure that out.

The URL parameter `p` specifies which API method is to be called.

API methods return a JSON object with two keys. The first, `result`, is a JSON object containing result data. This key is only available on some methods, and only when the operation has succeeded. The other, `errors`, is a JSON list. If it is empty, you can assume the operation succeeded.

API Methods
===========

`api_login`
-----------

###Example

`http://smoke.ajf.me/`
`?p=api_login`
`&user=joe`
`&password=foobar`
`&key=SERVER_SECRET_KEY`

(hopefully) resulting in:

    {
        "result": {
            "token": "XXX123"
        },
        "errors": []
    }

###Parameters

* `user` - username of user to log in for
* `password` - password of said user
* `key` - server's secret key (must be obtained by permission)

###Result object

If it succeeds, `result` will be a JSON object with a single key, `token`, the login token needed to use some other API methods.

###Possible error values

* `unknown_key` - unrecognised secret key
* `gg2_login_failed` - login failed to GG2F. Something's wrong with Sumochi or the GG2F, or they got their username and password wrong.
* `no_sumochi_user` - this person hasn't logged in for the first time to Sumochi (via the website) yet. They'll need to do this to create their account, essentially.


`api_give_achievement`
----------------------

###Example

`http://smoke.ajf.me/`
`?p=api_give_achievement`
`&user=joe`
`&token=XXX13`
`&a_key=SERVER_SECRET_KEY`
`&a_id=joesmod_1`
`&a_name=hello,%20world`
`&a_icon=c.png`

(hopefully) resulting in:

    {
        "errors": []
    }

###Parameters

* `user` - username of user to give achievement to
* `token` - login token
* `a_key` - server's secret key (must be obtained by permission)
* `a_id` - ID value for achievement - if another achievement with same ID exists, giving this achievement will fail with `already_has_achievement`. Recommended to use prefixes to reduce chance of accidental collision, e.g. `ajf_mod_1` not just `1`.
* `a_name` - Name of the achievement. Try to keep it ASCII, the font rendering may barf.
* `a_icon` - (optional) - references by name an icon stored on Sumochi server. Again, you'll have to ask me to add it. Some defaults include `baby.png` (overweight taunt icon), `c.png` (K&R C icon), `eyes.png` (eyes), `sandvich.png` (gg2 sandvich icon) and `test.png` (question mark)

###Possible error values

* `already_has_achievement` - already an achievement with same ID
* `unknown_key` - unrecognised secret key
* `invalid_token` - invalid login token
* `unknown_error` - unknown internal error
