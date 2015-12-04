<?php

function printArticleMediaWiki(&$article)
{
  if (is_array($article->description)) {
    $description = printTextRunAsMediaWiki($article->description);
	echo <<<EOT
      <div id="screenteps-article-description">
        $description
      </div>
EOT;
  }
  echo <<<EOT
      <div id="screenteps-steps-container">
      
EOT;
	foreach($article->steps as $step)
	{
		// Determine step H tag.
    $stepTag = ($step->level > 1) ? '===' : '==';
		$imageMarkup = '';
		$imageCaption = '';
		
		switch ($step->media->type)
    {
      case 'html':
        $hasImage = true;
        $imageMarkup = $step->media->html;
        break;
      
      default:
     		$hasImage = !empty($step->media->fullsize->relative_filename);
		
        if ($hasImage)
        {
          $hasThumbnail = is_array($step->media->thumbnail);
          $imageClass = $hasThumbnail ? ' screensteps-step-image-fullsize' : '';
            
          // Determine urls, width, etc. based on presence of thumbnail
          if ($hasThumbnail)
          {
            $imageURL = $step->media->thumbnail->relative_filename;
            $imageWidth = $step->media->thumbnail->width;
            $imageHeight = $step->media->thumbnail->height;
            $imageLink = $step->media->fullsize->relative_filename;
          } else {
            $imageURL = $step->media->fullsize->relative_filename;
            $imageWidth = $step->media->fullsize->width;
            $imageHeight = $step->media->fullsize->height;
            $imageLink = '';
          }
        
          // Get HTML for step image
          ob_start();
          echo <<<EOT
        <div class="screenteps-step-image-container{$imageClass}">
          [[File:{$imageURL}|class=screensteps-step-image|alt={$step->media_alt}|link={$imageLink}]]
        </div>
EOT;
          $imageMarkup = ob_get_clean();
        }
        break;
    }
		
		// Output
		$title = printTextRunAsMediaWiki($step->title, 'title');
		$instructions = printTextRunAsMediaWiki($step->instructions);
	
		echo <<<EOT
        <div id="step-{$step->id}" class="screenteps-step-container">
EOT;
		if (!empty($title))
		{
		echo <<<EOT
          {$stepTag} {$title} {$stepTag}
          
EOT;
		}
		if (empty($imageMarkup) && is_array($step->instructions)) {
			echo <<<EOT
          <div class="screenteps-step-instructions">$instructions</div>
          
EOT;
		} elseif (!empty($imageMarkup) && is_array($step->instructions)) {			
			if ($step->instructions_position == 'above') {
				echo <<<EOT
          <div class="screenteps-step-instructions">{$instructions}</div>
          
          {$imageMarkup}
EOT;
    		} else {
    			echo <<<EOT
          {$imageMarkup}
          <div class="screenteps-step-instructions">{$instructions}</div>
          
EOT;
    		}
		} elseif (!empty($imageMarkup)) {
			echo <<<EOT
          {$imageMarkup}
EOT;
  		}
  		echo <<<EOT
        </div>
        
EOT;
	} //foreach
	echo <<<EOT
      </div>
      
EOT;
}


function printTextRunAsMediaWiki($textrun, $type='instructions') {
  $output = '';
  $listDepth = 0;
  
  // If there is no text then just return an empty string.
  if (!is_array($textrun)) return '';
  
  // Iterate through paragraphs.
  foreach($textrun as $para)
  {
    $closingPara = '';
    
    /* Unused    
    $para->style->align  
    */  
    
    // Is this paragraph formatted as code?
    // If not is it a list item?
    if ($para->metadata->style == 'code')
    {
      $output .= '<code>';
      $closingPara = '</code>';
    } else {
      switch ($para->style->list_style)
      {
        case 'decimal':
          $output .= str_repeat('#', $para->style->list_depth) . ' ';
          break;
        default:
          $output .= str_repeat('*', $para->style->list_depth) . ' ';
          break;
      }
    }
    
    // Iterate through each text run in the paragraph.
    if (isset($para->runs))
    {
      foreach ($para->runs as $run)
      {
        $closingRun = '';
        $prefix = '';
        $suffix = '';
        $styles = explode(',', $run->style->font_styles);
      
        $hasBold = array_search('bold', $styles) != FALSE;
        $hasItalic = array_search('italic', $styles) != FALSE;
        $hasUnderline = array_search('underline', $styles) != FALSE;
      
        if (!empty($run->style->color))
        {
          $output .= '<span style="color: rgb(' . $run->style->color . ');">';
          $closingRun = '</span>';
        }
      
        if ($hasItalic) { $prefix .= "''"; $suffix = "''" . $suffix; }
        if ($hasBold) { $prefix .= "'''"; $suffix = "'''" . $suffix; }
        if ($hasUnderline) { $prefix .= '<ul>'; $suffix = '</ul>' . $suffix; }
      
        /* Unused
        $run->style->font_family
        $run->style->font_size
        $run->style->text_shift
        */
      
        $output .= $prefix;
        $output .= $run->text;
        $output .= $suffix;
        $output .= $closingRun;
      }
    }
        
    $output .= $closingPara;
    
    if ($type != 'title') $output .= PHP_EOL;
  }
  
  return $output;
}

?>