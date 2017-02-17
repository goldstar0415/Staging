//
//  MapViewController.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import UIKit
import SKMaps

class MapViewController: UIViewController {

    @IBOutlet weak var mapView: SKMapView!
    
    var currentUserLocation: CLLocation?
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view, typically from a nib.
        configureMap()
    }

    func configureMap() {
        mapView.settings.followUserPosition = false
        mapView.settings.rotationEnabled = false
        mapView.settings.showCompass = false
        
    
        SKPositionerService.sharedInstance().delegate = self
        SKPositionerService.sharedInstance().startLocationUpdate()
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }


}



