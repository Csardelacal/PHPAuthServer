<?php namespace client;

use IconModel;
use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

/* 
 * The MIT License
 *
 * Copyright 2021 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * A scope represents a permission that a user is giving an application when accessing
 * information on another application.
 * 
 * Whenever a user tries to log into an application using their account on this
 * identity server, the server will generate a token that includes access for the
 * scopes the client requested. (Whenever the resource owner approved it)
 * 
 * The host (audience) can then check whether the token has sufficient privileges
 * whenever the client sends it along with an API request.
 * 
 * @property string $identifier The id for the scope, this will be prefixed with the 
 *		app-id for the client in question. This is transmitted to the permission server
 * @property IconModel $icon The icon for the scope
 * @property string $caption A caption that users can understand
 * @property string $description Human readable description of the scope
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ScopeModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) 
	{
		$schema->identifier = new StringField(50);
		
		/*
		 * Icons are just a visual aid to help the user understand what they're 
		 * granting access to. If the icon is not set, the application should
		 * fall back to a standard icon.
		 */
		$schema->icon = new Reference(IconModel::class);
		
		/*
		 * The caption allows the user to quickly understand what the application
		 * wishes to have their authorization for.
		 */
		$schema->caption = new StringField(60);
		
		/*
		 * A description that the user can expand to understand better what they're
		 * opting into. By default this information will be collapsed, and only presented
		 * if the user requests it.
		 */
		$schema->description = new StringField(255);
		
		$schema->created = new IntegerField(true);
		$schema->updated = new IntegerField(true);
		
		$schema->index($schema->identifier)->unique();
	}
	
	public function onbeforesave(): void {
		parent::onbeforesave();
		
		if (!$this->created) {
			$this->created = time();
		}
		
		$this->updated = time();
	}

}
