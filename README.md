# lytix\_completions

This widget shows which activities have been marked as *completed* by participants.


## Visualisation

A table also containing a simple stacked bar (like a loading bar).


## JSON

The entries are connected by index: Each array’s entry at index `n` corresponds to the same activity.

```
{
    // Type of activity: quiz, forum, label, …
    Module: [ <string>, … ],
    Id: [ <int>, … ],
    // The name of the activity.
    Name: [ <string>, … ],
    // Number of students that have not completed the activity.
    Open: [ <int>, … ],
    // Number of students that have completed the activity.
    Done: [ <int>, … ]
}
```

### Example 
```
const testData = {
    // type of activity: quiz, forum, label, …
    Module: [ 'quiz', 'forum', 'feedback', 'forum', 'quiz', 'label', 'quiz' ],
    Id: [ 1, 2, 3, 4, 5, 6, 7 ],
    // the name of the activity
    Name: [
        'Quiz mit tollem Titel',
        'Ankündigungen',
        'Feedback Einheit 1',
        'Diskussionen',
        'Kurzes Quiz',
        'Wahnsinnig schwieriges Quiz',
        'Test'
    ],
    // number of students that have not completed the activity
    Open: [ 24, 100, 723, 1, 538, 14, 63 ],
    // number of students that have completed the activity
    Done: [ 356, 603, 24, 9999, 36, 57, 0 ]
};
```
