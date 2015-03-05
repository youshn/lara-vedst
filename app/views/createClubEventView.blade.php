{{-- Needs variables: templates, places, jobtypes --}}

@extends('layouts.master')

@section('title')
	Neue Veranstaltung erstellen
@stop

@section('content')

@if(Session::has('userId') 
	AND Session::has('userGroup')
	AND Session::get('userGroup') == 'marketing'
	OR Session::get('userGroup') == 'clubleitung')

		
	{{ Form::open(['method' => 'POST', 'route' => ['newClubEvent']]) }}

	<div class="row">
		<div class="container col-md-6">
			<div class="panel">
				<div class="panel-heading">
					<h4 class="panel-title">Neue Veranstaltung erstellen:</h4>
				</div>
				
				<br>	
	
				<div class="form-group">
					<label for="templateName" class="col-md-2 control-label">Vorlage: &nbsp;</label>
					<div class="col-md-6">
					@if (isset($activeTemplate))
						{{ Form::text('templateName', $activeTemplate, array('id'=>'templateName') ) }}
					@else
						{{ Form::text('templateName', '', array('id'=>'templateName') ) }}
					@endif
						&nbsp;&nbsp;
						<a class="btn-small btn-primary dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
					        <span class="caret"></span>
					    </a>
					    <ul class="dropdown-menu">
					    @foreach($templates as $template)
					        <li> 
					        	<a href="javascript:void(0);" 
					        	   onClick="document.getElementById('templateName').value='{{$template->schdl_title}}',
					        	   document.getElementById('confirmTemplate').href='{{ Request::getBasePath() }}/calendar/create/template/{{$template->id}}';">
					        	   {{$template->schdl_title}}</a>
					        </li>
						@endforeach
					    </ul>
				    </div>
				    <div class="col-md-4">
				    	<a class="btn-small btn-primary dropdown-toggle" id="confirmTemplate" href="#">
					        Vorlage aktivieren
					    </a>
					</div>
			   	</div>

			   	<br>

			   	<div class="form-group">
					<label for="saveAsTemplate" class="col-md-5 control-label">Als neue Vorlage speichern?</label>
			     	<div class="col-md-2">
						{{ Form::checkbox('saveAsTemplate', '1', false) }}
					</div>
			   	</div>

				<br>
				
				<div class="panel-body">
					<div class="form-group">
				      	<label for="title" class="col-md-2 control-label">Titel:</label>
				      	<div class="col-md-10">
				      		{{ Form::text( 'title', '', array('class'=>'form-control', 
				      										  'placeholder'=>'z.B. Weinabend',
				      										  'style'=>'cursor: auto',
				      										  'required') ) }}
				     	</div>
				    </div>
					
					<br><br>
				    
				    <div class="form-group">	
						<label for="subtitle" class="col-md-2 control-label">Untertitel:</label>
						<div class="col-md-10">
							{{ Form::text('subtitle', '', array('class'=>'form-control', 
				      										  'placeholder'=>'z.B. Das Leben ist zu kurz, um schlechten Wein zu trinken', 
				      										  'style'=>'cursor: auto') ) }}
						</div>
				    </div>
					
					<br><br>
				    
				    <div class="form-group">	
				     	<label for="isPrivate" class="col-md-4 control-label">Interne Veranstaltung?</label>
				     	<div class="col-md-8">
							{{ Form::checkbox('isPrivate', '1', false) }}&nbsp;
						</div>
				    </div>		    
					
					<br>

					<div class="form-group">
						<label for="place" class="col-md-2 control-label">Ort: &nbsp;</label>
						<div class="col-md-10">
						   	{{ Form::text('place', 'bc-Club', array('id'=>'place') ) }}
						 	<a class="btn-small btn-primary dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
						        <span class="caret"></span>
						    </a>
						    <ul class="dropdown-menu">
						    @foreach($places as $place)
						        <li> 
						        	<a href="javascript:void(0);" 
						        	   onClick="document.getElementById('place').value='{{$place}}'">{{$place}}</a>
						        </li>
							@endforeach
						    </ul>  	
					    </div>
				   	</div>
					
					<br>
				    
				    <div class="form-group">	
						<label for="beginDate" class="col-md-2 control-label">Beginn:</label>
						<div class="col-md-10">
							{{ Form::input('date', 'beginDate', date("Y-m-d")) }} um {{ Form::input('time', 'beginTime', '21:00') }}
						</div>
				    </div>

				    <div class="form-group">
						<label for="endDate" class="col-md-2 control-label">Ende:</label>
						<div class="col-md-10">
							{{ Form::input('date', 'endDate', date("Y-m-d")) }} um {{ Form::input('time', 'endTime', '01:00') }}
						</div>
				    </div>

				    <br><br><br><br>

				    <div class="form-group col-md-12">
				    	<label for="password" class="col-md-5">Passwort zum Eintragen:</label>
				    	<div class="col-md-7">
				    		{{ Form::password('password', '' ) }}
				    	</div>
				    </div>

				    <div class="form-group col-md-12">
				    	<label fro="passwordDouble" class="col-md-5">Passwort wiederholen:</label>
				    	<div class="col-md-7">
				    		{{ Form::password('passwordDouble', '') }}
				    	</div>
				    </div>

				</div>	
			</div>
		</div>

		<div class="container col-md-6">

			<div class="panel">
				<div class="panel-heading">
					<h4 class="panel-title">Weitere Infos:</h4>(öffentlich)
				</div>
				<div class="panel-body">				
				    <div class="form-group">	
						<div class="col-md-12">
							{{ Form::textarea('publicInfo', '', array('class'=>'form-control', 
																	  'rows'=>'5',
																	  'placeholder'=>'z.B. Karten nur im Vorverkauf') ) }}
						</div>
					</div>	
				</div>
			</div>

			<div class="panel">
				<div class="panel-heading">
					<h4 class="panel-title">Details:</h4>(nur intern sichtbar)
				</div>
				<div class="panel-body">
				    <div class="form-group">
						<div class="col-md-12">
							{{ Form::textarea('privateDetails', '', array('class'=>'form-control', 
																		  'rows'=>'5', 
																		  'placeholder'=>'z.B. DJ-Tisch wird gebraucht') ) }}
						</div>
				    </div>
				</div>
			</div>
		</div>
	</div>
	
	@include('partials.editSchedule')
	<br>
	{{ Form::submit('Veranstaltung mit Dienstplan speichern', array('class'=>'btn btn-primary')) }}
	&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="javascript:history.back()" class="btn btn-default">Ohne Änderung zurück</a>

	{{ Form::close() }}
	
@else
	@include('partials.accessDenied')
@endif
@stop


