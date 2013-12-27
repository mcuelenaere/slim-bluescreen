# Slim Bluescreen

This is a port of the awesome blue screen provided by the [Nette Framework](http://nette.org/).

## Getting started

### Installation

Add this package as a requirement to your `composer.json`:

	"require": {
		"mcuelenaere/slim-bluescreen": "dev-master"
	}

### Usage

#### Slim 2.x

Replace the default Slim exception middleware with the one provided by this project:

```diff
--- Slim.old.php	2013-12-27 20:08:50.000000000 +0100
+++ Slim.new.php	2013-12-27 20:09:13.000000000 +0100
@@ -1254,7 +1254,7 @@
         //Apply final outer middleware layers
         if ($this->config('debug')) {
             //Apply pretty exceptions only in debug to avoid accidental information leakage in production
-            $this->add(new \Slim\Middleware\PrettyExceptions());
+            $this->add(new \SlimBluescreen\BlueScreenMiddleware());
         }

         //Invoke middleware and application stack
```

#### Slim 3.x

```php
// instantiate BlueScreen object
$bluescreen = new \SlimBluescreen\BlueScreen();

// register error handler with Slim app
$app->error(array($bluescreen, 'render'));
```

## License

This package is released under the New BSD License:

```
Copyright (c) 2013, Maurus Cuelenaere
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the <organization> nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
```

### Nette Framework

This package contains modified sources of the Nette Framework, which is [dual-licensed](https://raw.github.com/nette/nette/master/license.md)
as the New BSD License or the GNU General Public License (GPL) version 2 or 3.

```
Copyright (c) 2004, 2013 David Grudl (http://davidgrudl.com)
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

	* Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.

	* Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.

	* Neither the name of "Nette Framework" nor the names of its contributors
	may be used to endorse or promote products derived from this software
	without specific prior written permission.

This software is provided by the copyright holders and contributors "as is" and
any express or implied warranties, including, but not limited to, the implied
warranties of merchantability and fitness for a particular purpose are
disclaimed. In no event shall the copyright owner or contributors be liable for
any direct, indirect, incidental, special, exemplary, or consequential damages
(including, but not limited to, procurement of substitute goods or services;
loss of use, data, or profits; or business interruption) however caused and on
any theory of liability, whether in contract, strict liability, or tort
(including negligence or otherwise) arising in any way out of the use of this
software, even if advised of the possibility of such damage.
```