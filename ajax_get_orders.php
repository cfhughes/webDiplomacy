<?php
//define('AJAX', true);

require_once('header.php');

header('Content-type: application/json');
header("Access-Control-Allow-Origin: *");

if ( ! isset($_REQUEST['gameID']) )
{
    header('HTTP/1.1 400 Bad Request');
    die("No Game Specified");
}

$gameID = (int)$_REQUEST['gameID'];

try
{
    require_once(l_r('objects/game.php'));
    //require_once(l_r('board/chatbox.php'));
    require_once(l_r('gamepanel/gameboard.php'));

    $Variant=libVariant::loadFromGameID($gameID);
    libVariant::setGlobals($Variant);
    $Game = $Variant->panelGameBoard($gameID);

    // If viewing an archive page make that the title, otherwise us the name of the game
    //libHTML::starthtml(isset($_REQUEST['viewArchive'])?$_REQUEST['viewArchive']:$Game->titleBarName());

    if ( $Game->Members->isJoined() )
    {
        // We are a member, load the extra code that we might need
        //require_once(l_r('gamemaster/gamemaster.php'));
        require_once(l_r('board/member.php'));
        require_once(l_r('board/orders/orderinterface.php'));

        global $Member;
        $Game->Members->makeUserMember($User->id);
        $Member = $Game->Members->ByUserID[$User->id];
    }
}
catch(Exception $e)
{
    // Couldn't load game
    libHTML::error(l_t("Couldn't load specified game; this probably means this game was cancelled or abandoned.")." ".
        ($User->type['User'] ? l_t("Check your <a href='index.php' class='light'>notices</a> for messages regarding this game."):''));
}

if( isset($Member) && $Member->status == 'Playing' && $Game->phase!='Finished' )
{
    /*if( $Game->phase != 'Pre-game' )
    {
        if(isset($_REQUEST['Unpause'])) $_REQUEST['Pause']='on'; // Hack because Unpause = toggle Pause

        foreach(Members::$votes as $possibleVoteType) {
            if( isset($_REQUEST[$possibleVoteType]) && isset($Member) && libHTML::checkTicket() )
                $Member->toggleVote($possibleVoteType);
        }
    }

    $DB->sql_put("COMMIT");*/

    /*if( $Game->processStatus!='Crashed' && $Game->processStatus!='Paused' && $Game->attempts > count($Game->Members->ByID)/2+4  )
    {
        require_once(l_r('gamemaster/game.php'));
        $Game = $Game->Variant->processGame($Game->id);
        $Game->crashed();
        $DB->sql_put("COMMIT");
    }
    else
    {
        if( $Game->Members->votesPassed() && $Game->phase!='Finished' )
        {
            $DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$Game->id);
            $DB->sql_put("COMMIT");

            require_once(l_r('gamemaster/game.php'));
            $Game = $Game->Variant->processGame($Game->id);
            try
            {
                $Game->applyVotes(); // Will requery votesPassed()
                $DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$Game->id);
                $DB->sql_put("COMMIT");
            }
            catch(Exception $e)
            {
                if( $e->getMessage() == "Abandoned" || $e->getMessage() == "Cancelled" )
                {
                    assert('$Game->phase=="Pre-game" || $e->getMessage() == "Cancelled"');
                    $DB->sql_put("COMMIT");
                    libHTML::notice(l_t('Cancelled'), l_t("Game was cancelled or didn't have enough players to start."));
                }
                else
                    $DB->sql_put("ROLLBACK");

                throw $e;
            }
        }
        else if( $Game->needsProcess() )
        {
            $DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$Game->id);
            $DB->sql_put("COMMIT");

            require_once(l_r('gamemaster/game.php'));
            $Game = $Game->Variant->processGame($Game->id);
            if( $Game->needsProcess() )
            {
                try
                {
                    $Game->process();
                    $DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$Game->id);
                    $DB->sql_put("COMMIT");
                }
                catch(Exception $e)
                {
                    if( $e->getMessage() == "Abandoned" || $e->getMessage() == "Cancelled" )
                    {
                        assert('$Game->phase=="Pre-game" || $e->getMessage() == "Cancelled"');
                        $DB->sql_put("COMMIT");
                        libHTML::notice(l_t('Cancelled'), l_t("Game was cancelled or didn't have enough players to start."));
                    }
                    else
                        $DB->sql_put("ROLLBACK");

                    throw $e;
                }
            }
        }
    }*/

/*    if( $Game instanceof processGame )
    {
        $Game = $Game->Variant->panelGameBoard($Game->id);
        //$Game->Members->makeUserMember($User->id);
        //$Member = $Game->Members->ByUserID[$User->id];
    }*/

    if ( 'Pre-game' != $Game->phase && $Game->phase!='Finished' )
    {
        $OI = OrderInterface::newBoard();
        $OI->load();

        //$Orders = '<div id="orderDiv'.$Member->id.'">'.$OI->html().'</div>';

        echo $OI->JSONOrders();

        unset($OI);
    }
}