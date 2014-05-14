This is the complete source code for the prototype solution developed in my master's thesis 2009.

In order to use this code on a separate sever is there a lot of work to be done. Most configuration is found in the config class, but the JavaScript is notoriously packed with pointers to files on the disk. A better solution should probably have been made, but this wasn't exactly the focus of the thesis. 

The databasetables.sql files contains SQL statements to set up the needed database tables, remember that the PostGIS spatial extention to PostgreSQL is needed. 
The gfx directory includes most of the graphics uses in the prototype, and the final folder contains the actual source code.

This source code is licensed under the MIT License:

	Copyright (c) 2009 Atle Frenvik Sveen

	Permission is hereby granted, free of charge, to any person
	obtaining a copy of this software and associated documentation
	files (the "Software"), to deal in the Software without
	restriction, including without limitation the rights to use,
	copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the
	Software is furnished to do so, subject to the following
	conditions:

	The above copyright notice and this permission notice shall be
	included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
	OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
	HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	OTHER DEALINGS IN THE SOFTWARE.

For updated versions and other news, check http://code.atlefren.net/masters

For the accompanying report, see http://docs.atlefren.net

If you wish to contact me, I am available at atle@frenviksveen.net