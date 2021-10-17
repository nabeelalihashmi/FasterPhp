# FasterPHP
## A low overhead PHP framework for APIs.


```
 Turn off the composer autoload and use own libs to increase performance by 3X;
```

FasterPHP is a Route-Oriented Framework for Writing Php apps. Every file in Routes folder is a route and corresponding get, post, put, delete, head functions are handler for request method.


### Installation
  Clone the repository.

  ```
    git clone https://github.com/nabeelalihashmi/FasterPhp.git
  ```
  And start writing code.
### Folder Structure
* app/Routes


### Middlware
Before Middleware = before_get(). The Middleware function must return true to exectue. 


### Points

* Desgin API endpoints from last point first

like
```
/api/add/half/divide/Ten/20
Filename: app/Routes/add/half/divide/Ten.php
[OR]      app/Routes/add/half/divide/Ten/index.php
Function: get(number, Request $request Response $response);


/api/add/half/divide/20/20
Filename: app/Routes/add/half/divide.php
[OR]      app/Routes/add/half/divide/index.php
Function: get(number1, number2, Request $request Response $response);

/api/add/half/20/20
Filename: app/Routes/add/half.php
[OR]      app/Routes/add/half/index.php
Function: get(number1, number2, Request $request Response $response);

/api/add/10/20
Filename: app/Routes/add.php
[OR]      app/Routes/add/index.php
Function: get(number1, number2, Request $request Response $response);
```

### How These Will Exectue:

```
/api/add/half/divide/Ten/2

Check if 
/api/add/half/divide/Ten/2 
IF "2" IS FOLDER
IS NOT FOLDER => Check if it is file -> OK
```