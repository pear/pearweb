<?PHP

    require_once 'pepr/pepr.php';
    
    if (empty($_GET['id'])) {
        localRedirect('/pepr/pepr-overview.php');
    }
    
    response_header('PEPr :: Proposal comments');
        
    $proposal = proposal::get($dbh, $_GET['id']);
    if (PEAR::isError($proposal)) {
        PEAR::raiseError('Proposal '.$_GET['id'].' not found.');
    }

    $comments = ppComment::getAll($proposal->id, 'package_proposal_comments');
    $userInfos = array();
    
    $commentsHTML = "";
    if (is_array($comments) && (count($comments) > 0)) {
        foreach ($comments as $comment) {
            if (empty($userInfos[$comment->user_handle])) {
                $userInfos[$comment->user_handle] = user::info($comment->user_handle);
            }
            $commentsHTML .= '<strong>'.user_link($comment->user_handle);
            $commentsHTML .= ' ['. date('Y-m-d, H:i', $comment->timestamp) .']</strong><br />';
            $commentsHTML .= nl2br($comment->comment).'<br /><br />';
        }
    } else {
        $commentsHTML = 'Sorry, there are no comments available.';
    }
    
    echo make_link('/pepr/pepr-proposal-show.php?id='.$_GET['id'], 'Back to proposal');
    
    $bb = new BorderBox('Proposal comments', '80%', '', 2, true);
    
    $bb->horizHeadRow('Category:', $proposal->pkg_category);
    $bb->horizHeadRow('Package:', $proposal->pkg_name);
    $bb->horizHeadRow('Proposer:', user_link($proposal->user_handle));
    
    $bb->fullRow($commentsHTML);
    
    $bb->end();
    
    echo make_link('/pepr/pepr-proposal-show.php?id='.$_GET['id'], 'Back to proposal');
    
    response_footer();

?>
