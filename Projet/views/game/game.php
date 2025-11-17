
<section class="courses_area p_120" style="background: #f9f9f9;">
    <div class="container">
        <div class="main_title text-center">
            <h2>Fill in the Blanks</h2>
            <p>Glissez les mots ci-dessous et déposez-les dans les espaces vides du texte.</p>
        </div>
        
        <?php if (isset($feedback) && !empty($feedback)): ?>
        <div class="alert alert-<?php echo isset($feedback['class']) && $feedback['class'] === 'success' ? 'success' : 'danger'; ?> text-center" role="alert" style="margin-bottom: 30px;">
            <?php echo htmlspecialchars($feedback['message']); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo BASE_URL; ?>game/submit" id="gameForm">
            <div class="text-container" id="textContainer">
                <?php 
                $gameData = isset($gameData) ? $gameData : [];
                $text = isset($gameData['text']) ? $gameData['text'] : [];
                foreach ($text as $line) {
                    echo $line . ' ';
                }
                ?>
            </div>
            
            <div class="words-pool" id="wordsPool">
                <h4 class="text-center mb-4">Glissez les mots  :</h4>
                <div class="words-list">
                    <?php 
                    $words = isset($gameData['words']) ? $gameData['words'] : [];
                    foreach ($words as $word) {
                        $wordId = isset($word['id']) ? $word['id'] : 0;
                        $wordText = isset($word['word']) ? $word['word'] : (isset($word['text']) ? $word['text'] : '');
                        $wordOrder = isset($word['order_correct']) ? $word['order_correct'] : (isset($word['order']) ? $word['order'] : 0);
                        echo '<div class="word" draggable="true" data-id="' . htmlspecialchars($wordId) . '" data-order="' . htmlspecialchars($wordOrder) . '">' . htmlspecialchars($wordText) . '</div>';
                    }
                    ?>
                </div>
            </div>
            
            <div id="hiddenInputs"></div>
            
            <div class="text-center">
                <button type="submit" class="main_btn" id="submitBtn" name="submit">Soumettre les réponses</button>
            </div>
        </form>
    </div>
</section>


<style>
.text-container {
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    min-height: 200px;
    border: 2px solid #4AB1AC;
    font-size: 18px;
    line-height: 2;
}

.blank {
    display: inline-block;
    min-width: 150px;
    height: 45px;
    border: 3px dashed #4AB1AC;
    margin: 0 8px;
    text-align: center;
    vertical-align: middle;
    background: #fff;
    cursor: pointer;
    border-radius: 8px;
    line-height: 45px;
    transition: all 0.3s ease;
}

.blank:hover {
    background: #EEEEEE;
    border-color: #EEBA0B;
    transform: scale(1.05);
}

.blank.filled {
    border-style: solid;
    border-color: #4AB1AC;
    background: linear-gradient(135deg, #4AB1AC 0%, #3a9a94 100%);
    color: white;
    font-weight: bold;
}

.blank.filled::after {
    content: '✓';
    position: absolute;
    right: 5px;
    color: #EEBA0B;
    font-size: 1.2em;
}

.words-pool {
    background: #EEEEEE;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    border: 2px dashed #4AB1AC;
}

.words-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
}

.word {
    background: linear-gradient(135deg, #EEBA0B 0%, #d4a509 100%);
    color: #333;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: grab;
    font-weight: 600;
    font-size: 1.1em;
    box-shadow: 0 4px 10px rgba(238, 186, 11, 0.3);
    transition: all 0.3s ease;
}

.word:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(238, 186, 11, 0.5);
}

.word:active {
    cursor: grabbing;
}

.word.dragged {
    opacity: 0.4;
}

#submitBtn:disabled {
    background: #EEEEEE !important;
    color: #999 !important;
    cursor: not-allowed;
}

.hidden-text {
    display: none;
}

@media (max-width: 768px) {
    .text-container {
        font-size: 16px;
        padding: 20px;
    }
    .blank {
        min-width: 120px;
        height: 40px;
        line-height: 40px;
    }
}
</style>

<script src="<?php echo BASE_URL; ?>Projet/views/game/game.js"></script> 