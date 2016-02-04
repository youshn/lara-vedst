<?php

namespace Lara\Http\Controllers;

use Request;
use Session;
use Input;
use Hash;
use Illuminate\Database\Eloquent\Collection;

use Lara\Jobtype;
use Lara\Schedule;
use Lara\ScheduleEntry;

use Carbon\Carbon;
use \Datetime;

use Lara\Http\Requests;
use Lara\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

   /**
    * Update the specified resource in storage.
    * Edit or create a schedule with its entered information.
    * If $scheduleId is null create a new schedule, otherwise the schedule specified by $scheduleId will be edited.
    *
    * Should be static to be accessed from ClubEventController
    *
    * @param int $scheduleId
    * @return Schedule newSchedule
     */
    public static function update($scheduleId)
    {
        $schedule = new Schedule;

        if (!is_null($scheduleId))
        {
            $schedule = Schedule::findOrFail($scheduleId);
        }

        // format: time; validate on filled value
        if(!empty(Input::get('preparationTime'))) 
        {
            $schedule->schdl_time_preparation_start = Input::get('preparationTime');
        }
        else
        { 
            $schedule->schdl_time_preparation_start = mktime(0, 0, 0);
        }

        // format: date; validate on filled value
        // by tasks - stay this way, by schedules will be set to null by event creation.
        // ToDo: evtl statt null auf today setzen? M.
        if(!empty(Input::get('dueDate')))
        {
            $dueDate = new DateTime(Input::get('dueDate'),
                       new DateTimeZone(Config::get('app.timezone')));
            $schedule->schdl_due_date = $dueDate->format('Y-m-d');
        }
        else 
        {
            $schedule->schdl_due_date = date('Y-m-d', time());
        }

        // format: password; validate on filled value
        if (Input::get('password') == "delete" 
        AND Input::get('passwordDouble') == "delete") 
        {
            $schedule->schdl_password = '';
        } 
        elseif (!empty(Input::get('password'))
            AND !empty(Input::get('passwordDouble'))
            AND Input::get('password') == Input::get('passwordDouble')) 
        {
            $schedule->schdl_password = Hash::make(Input::get('password'));
        }

        // format: tinyInt; validate on filled value
        if (Input::get('saveAsTemplate') == true)
        {
            $schedule->schdl_is_template = true;
            $schedule->schdl_title = Input::get('templateName');
        }
        else 
        {
            $schedule->schdl_is_template = false;
        }

        return $schedule;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    /**
     * Updates entry revision
     *
     * @param Schedule $schedule     
     * @param ScheduleEntry $entry
     * @param string $action
     * @param string $old
     * @param string $new
     * @return void
     */
    public static function logRevision($schedule, $entry, $action, $old, $new)
    {
        // workaround for older events where revision history is not present
        if($schedule->entry_revisions == "")
        {
            $schedule->entry_revisions = json_encode(["0" => ["entry id" => "",
                                                      "job type" => "",
                                                      "action" => "Keine frühere Änderungen vorhanden.",
                                                      "old id" => "",
                                                      "old value" => "",
                                                      "new id" => "",
                                                      "new value" => "",
                                                      "user id" => "",
                                                      "user name" => "",
                                                      "from ip" => "",
                                                      "timestamp" => (new DateTime)->format('d.m.Y H:i:s')]]);
        }
    
        // decode revision history
        $revisions = json_decode($schedule->entry_revisions, true);

        // decode old values
        if(!is_null($old)){
            $oldId = $old->id;

            switch ($old->prsn_status) {
                case "candidate":
                    $oldStatus = "(K)";
                    break;
                case "member":
                    $oldStatus = "(A)";
                    break;
                case "veteran":
                    $oldStatus = "(V)";
                    break;
                default: 
                    $oldStatus = "";
            }

            $oldName = $old->prsn_name
                     . $oldStatus 
                     . "(" . $old->getClub->clb_title . ")";
        }
        else
        {
            $oldId = "";
            $oldName = "";
        }

        // decode new values
        if(!is_null($new)){
            $newId = $new->id;
            
            switch ($new->prsn_status) {
                case "candidate":
                    $newStatus = "(K)";
                    break;
                case "member":
                    $newStatus = "(A)";
                    break;
                case "veteran":
                    $newStatus = "(V)";
                    break;
                default: 
                    $newStatus = "";
            }

            $newName = $new->prsn_name 
                     . $newStatus
                     . "(" . $new->getClub->clb_title . ")";
        }
        else
        {
            $newId = "";
            $newName = "";
        }
        
        // append current change
        array_push($revisions, ["entry id" => $entry->id,
                                "job type" => $entry->getJobType->jbtyp_title,
                                "action" => $action,
                                "old id" => $oldId,
                                "old value" => $oldName,
                                "new id" => $newId,
                                "new value" => $newName,
                                "user id" => Session::get('userId') != NULL ? Session::get('userId') : "",
                                "user name" => Session::get('userId') != NULL ? Session::get('userName') . '(' . Session::get('userClub') . ')' : "Gast",
                                "from ip" => Request::getClientIp(),
                                "timestamp" => (new DateTime)->format('d.m.Y H:i:s')]
                    );      

        // encode and save
        $schedule->entry_revisions = json_encode($revisions);
                        
        $schedule->save();
    }


    /**
    * Create all new scheduleEntries with entered information.
    *
    * @return Collection scheduleEntries
    */
    public static function createScheduleEntries()
    {
        $scheduleEntries = new Collection;

        // parsing jobtype entries
        for ($i=1; $i <= Input::get("counter"); $i++) {

            // skip empty fields
            if (!empty(Input::get("jobType" . $i))) 
            {       

                // check if job type exists
                $jobType = Jobtype::where('jbtyp_title', '=', Input::get("jobType" . $i))
                                  ->where('jbtyp_time_start', '=', Input::get("timeStart" . $i))
                                  ->where('jbtyp_time_end', '=', Input::get("timeEnd" . $i))
                                  ->first();
                
                // If not found - create new jpb type with data provided
                if (is_null($jobType))
                {
                    // TITLE
                    $jobType = Jobtype::create(array('jbtyp_title' => Input::get("jobType" . $i)));

                    // TIME START
                    $jobType->jbtyp_time_start = Input::get('timeStart' . $i);

                    // TIME END
                    $jobType->jbtyp_time_end = Input::get('timeEnd' . $i);

                    // STATISTICAL WEIGHT
                    $jobType->jbtyp_statistical_weight = Input::get('jbtyp_statistical_weight' . $i);

                    // NEEDS PREPARATION
                    $jobType->jbtyp_needs_preparation = 'true';

                    // ARCHIVED set to "false"
                    $jobType->jbtyp_is_archived = 'false';

                    $jobType->save();
                }

                $scheduleEntry = new ScheduleEntry;
                $scheduleEntry->jbtyp_id = $jobType->id;

                // save changes
                $scheduleEntries->add(ScheduleController::updateScheduleEntry($scheduleEntry, $jobType->id, $i));
            }
        }

        return $scheduleEntries;
    }


    /**
    * Update start and end time of $newScheduleEntry with input of gui elements
    *
    * @param ScheduleEntry $scheduleEntry
    * @param int $jobtypeId
    * @param int $counterValue
    * @return ScheduleEntry updates scheduleentry
    */
    private static function updateScheduleEntry($scheduleEntry, $jobtypeId, $counterValue)
    {
        $scheduleEntry->entry_time_start = Input::get('timeStart' . $counterValue);

        $scheduleEntry->entry_time_end = Input::get('timeEnd' . $counterValue);

        $scheduleEntry->entry_statistical_weight = Input::get('jbtyp_statistical_weight' . $counterValue);

        return $scheduleEntry;
    }

    
}
