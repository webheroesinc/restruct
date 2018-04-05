
import logging
import json

from .				import Restruct

log				= logging.getLogger("tests")
log.setLevel( logging.DEBUG )

database			= []
database.append({
    "id": 1,
    "first": 'Travis',
    "last": 'Mottershead',
    "email": 'travis@pitch.so',
    "gender": 'm',
    "age": 20,
    "weight": 150
})
database.append({
    "id": 2,
    "first": 'Matthew',
    "last": 'Brisebois',
    "email": 'matthew@pitch.so',
    "gender": 'm',
    "age": 25,
    "weight": 210
})
database.append({
    "id": 3,
    "first": 'Geoff',
    "last": 'Dick',
    "email": 'geoff@pitch.so',
    "gender": 'm',
    "age": 28,
    "weight": 130
})
database.append({
    "id": 4,
    "first": 'Valerie',
    "last": 'Brisebois',
    "email": 'valerie@brisebois.me',
    "gender": 'f',
    "age": 24,
    "weight": 120
})



def test_restruct_collection():

    collection			= Restruct.collection(database)
    
    result = collection.format({
        "= 'adults' if self['age'] > 18 else 'kids'": [
            ["= self['age'] > 25", "{{ first }}"]
        ]
    })
    expected = {
        "adults": [
            [False, "Travis"],
            [False, "Matthew"],
            [True, "Geoff"],
            [False, "Valerie"]
        ]
    }

    log.debug("{}".format(json.dumps(result, indent=4)))
    log.debug("{}".format(json.dumps(expected, indent=4)))

    assert result == expected

    
    result = collection.format({
	"= 'best_friend' if self['first'] == 'Travis' else None": {
	    "name": "{{first}} {{last}}",
	    "hotness": "0% *******-------------------------- 100%"
	}
    })
    expected = {
        "best_friend": {
            "name": "Travis Mottershead",
            "hotness": "0% *******-------------------------- 100%"
        }
    }
    
    log.debug("{}".format(json.dumps(result, indent=4)))
    log.debug("{}".format(json.dumps(expected, indent=4)))

    assert result == expected

    
    result = collection.format({
	"= 'other_friends' if not self['first'] == 'Travis' else None": [{
	    "name": "{{first}} {{last}}",
	    "hotness": "0% *-------------------------------- 100%"
	}]
    })
    expected = {
        "other_friends": [
            {
                "name": "Matthew Brisebois",
                "hotness": "0% *-------------------------------- 100%"
            },
            {
                "name": "Geoff Dick",
                "hotness": "0% *-------------------------------- 100%"
            },
            {
                "name": "Valerie Brisebois",
                "hotness": "0% *-------------------------------- 100%"
            }
        ]
    }
    
    log.debug("{}".format(json.dumps(result, indent=4)))
    log.debug("{}".format(json.dumps(expected, indent=4)))

    assert result == expected


    @Restruct.method()
    def weightClass(self, w):
        return '200+' if w >= 200 else '0-199'
    
    result = collection.format({
	"= weightClass( self['weight'] )": {
	    "{{id}}": {
		"name": "{{first}} {{last}}",
		"weight": "< weight"
	    }
	}
    })
    expected = {
        "200+": {
            "2": {
                "name": "Matthew Brisebois",
                "weight": 210
            }
        },
        "0-199": {
            "1": {
                "name": "Travis Mottershead",
                "weight": 150
            },
            "4": {
                "name": "Valerie Brisebois",
                "weight": 120
            },
            "3": {
                "name": "Geoff Dick",
                "weight": 130
            }
        }
    }
    
    log.debug("{}".format(json.dumps(result, indent=4)))
    log.debug("{}".format(json.dumps(expected, indent=4)))

    assert result == expected

    
    result = collection.format({
	"emails": {
	    "__array": True,
	    "< id": "< email"
	}
    })
    expected = {
        "emails": [
            "travis@pitch.so",
            "matthew@pitch.so",
            "geoff@pitch.so",
            "valerie@brisebois.me"
        ]
    }
    
    log.debug("{}".format(json.dumps(result, indent=4)))
    log.debug("{}".format(json.dumps(expected, indent=4)))

    assert result == expected

    
def test_restruct_collection():

    collection			= Restruct.collection(database)
    
    @Restruct.method()
    def frame_info(self):
        return {
	    "index": self.index,
	    "data": self.data,

	    "check": self.source(self.index),

	    "parent": repr(self.parent),
	    "source": self.source(),
	    
	    "keys": self.keys(),
	    "values": self.values(),
	    "id": repr(self.child('id')),
	    "name": repr(self.child('name')),
	    "children": repr(self.children()),
        }
    
    result = collection.format({
	"< id": "= frame_info()"
    })
    expected = None
    
    log.debug("{}".format(json.dumps(result, indent=4)))
    log.debug("{}".format(json.dumps(expected, indent=4)))

    assert result == expected
