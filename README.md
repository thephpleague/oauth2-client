


### List of identity providers

| Provider         | uid    | nickname | name   | first_name | last_name | email  | location | description | imageUrl | urls                            |
| :-------------   | :----- | :------- | :----- | :--------- | :-------- | :----- | :------- | :---------- | :------- | :---------------------          |
| **Blooie**       | string | string   | string | string     | string    | string | string   | string      | string   | array (Facebook)                |
| **Facebook**     | string | string   | string | string     | string    | string | string   | string      | string   | array (Facebook)                |
| **Foursquare**   | string | null     | string | string     | string    | string | string   | null        | string   | array (Foursquare)              |
| **Github**       | string | string   | string | null       | null      | string | null     | null        | null     | array (Github, [personal])      |
| **Google**       | string | string   | string | string     | string    | string | null     | null        | string   | null                            |
| **Instagram**    | string | string   | string | null       | null      | null   | null     | null        | string   | array ([personal])              |
| **Mailchimp**    | string | null     | null   | null       | null      | null   | null     | null        | null     | null                            |
| **Mailru**       | string | string   | string | string     | string    | string | null     | null        | string   | null                            |
| **Paypal**       | string | string   | sting  | string     | string    | string | string   | null        | null     | array (Paypal)                  |
| **Soundcloud**   | string | string   | string | null       | null      | null   | string   | string      | string   | array ([Myspace], [personal])   |
| **Vkontakte**    | null   | string   | string | string     | string    | null   | null     | null        | string   | null                            |
| **Windows Live** | string | string   | string | null       | null      | null   | string   | null        | null     | array(Windows Live)             |
| **Yandex**       | string | string   | string | string     | string    | string | string   | string      | string   | null                            |

**Notes**: Providers which return URLs sometimes include additional URLs if the user has provided them. These have been wrapped in []